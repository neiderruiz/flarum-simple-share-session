<?php

namespace Neiderruiz\SimpleShareSession;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Flarum\User\User;
use Flarum\Http\Rememberer;
use Flarum\Http\SessionAuthenticator;
use Illuminate\Support\Arr;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Flarum\Http\SessionAccessToken;
use Flarum\Http\RememberAccessToken;
use Flarum\User\Event\LoggedIn;
use Laminas\Diactoros\Response\RedirectResponse;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Command\RegisterUser;
use Illuminate\Contracts\Bus\Dispatcher;


class AuthMiddleware implements MiddlewareInterface
{
    protected $authenticator;
    protected $rememberer;
    protected $settings;

    public function __construct(SessionAuthenticator $authenticator, Rememberer $rememberer,SettingsRepositoryInterface $settings)
    {
        $this->authenticator = $authenticator;
        $this->rememberer = $rememberer;
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $actor = $request->getAttribute('actor');
        $endpoint_check_session = $this->settings->get('neiderruiz_fsss.verify_session_url');

        if ($actor && $actor->isGuest() === false) {
            return $handler->handle($request); // user is already logged in, continue normally
        }

        // get the session id from the Website cookies
        $cookies = $request->getCookieParams();
        $sessionId = $cookies['sessionid'] ?? null;


        if ($sessionId) {
            // verify the session with the API
            $responseUser = $this->verifySessionApi($sessionId);

            if ($responseUser) {
                // Buscar al usuario en Flarum por su email
                $user = User::where('email', $responseUser['email'])->first();

                if(!$user) {

                    $data = [
                        'attributes' => [
                            'username' => $responseUser['username'] ?? $responseUser['email'],
                            'email' => $responseUser['email'],
                            'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                            'is_email_confirmed' => true,
                        ],
                    ];

                    $bus = resolve(Dispatcher::class);
                    $user = $bus->dispatch(new RegisterUser($actor, $data));

                }

                $auto_comfirm_accounts = $this->settings->get('neiderruiz_fsss.auto_confirm_accounts');

                if($auto_comfirm_accounts){
                    $user->activate();
                    $user->save();
                }

                if ($user) {
                    // ✅ Create a session access token
                    $accessToken = SessionAccessToken::generate($user->id);
                    $accessToken->save();

                    // ✅ Create a session remember token
                    $rememberToken = RememberAccessToken::generate($user->id);
                    $rememberToken->save();

                    // ✅ Log in to Flarum
                    $session = $request->getAttribute('session');
                    $this->authenticator->logIn($session, $accessToken);

                    // ✅ Fire the LoggedIn event
                    event(new LoggedIn($user, $accessToken));

                    // ✅ Redirect only if the user doesn't come from the same page
                    $redirectUrl = $request->getHeaderLine('Referer') ?: '/';
                    return new RedirectResponse($redirectUrl);
                }
            }
        }

        // If no valid session, continue with normal request handling
        return $handler->handle($request);
    }

    private function verifySessionApi(string $sessionId): ?array
    {
        $client = new Client();

        try {
            $endpoint_check_session = $this->settings->get('neiderruiz_fsss.verify_session_url');

            $response = $client->get($endpoint_check_session, [
                'query' => ['sessionid' => $sessionId],
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 5,
            ]);

            $data = json_decode($response->getBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $data['user'] ?? null;
        } catch (RequestException $e) {
            error_log("Error connecting to the API: {$e->getMessage()}");
            return null;
        }
    }
}
