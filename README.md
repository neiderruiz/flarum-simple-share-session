
# install package

```bash
composer install neiderruiz/flarum-simple-share-session
```

## enable extension on Flarum

the extension will check the session id from the cookie and send a request to the API to verify the session.

```bash
$cookies = $request->getCookieParams();
$sessionId = $cookies['sessionid'] ?? null;
```

add endpoint to check session on Falrum extension settings

```bash
https://domain.com/api/verify-session
```

Your endpoint will receive the parameter through the url for example:

`https://domain.com/api/verify-session?sessionid=123456789`

# expected response

```json
{
    "user": {
        "id": 1,
        "username": "admin",
        "email": "admin@domain.com",
        "name": "Admin",
    }
}
```


# developent config

```bash
npm run build
```

# refresh changes
```bash
composer update neiderruiz/flarum-simple-share-session *@dev
```

```bash
php flarum cache:clear                                
php flarum assets:publish
```

