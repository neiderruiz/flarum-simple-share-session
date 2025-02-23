
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


## Support

If you have any questions, need help, or want to contribute, feel free to reach out:

- **Website**: [neiderruiz.com](https://neiderruiz.com)
- **GitHub**: [@neiderruiz](https://github.com/neiderruiz)
- **Twitter**: [@neiderruiz](https://x.com/neiderruiz_)
- **YouTube**: [Neider Ruiz](https://youtube.com/@neiderruiz)


## Support the Project

If you find this project useful, consider buying me a coffee to help me keep improving it. Your support means a lot! ☕

[![Buy me a coffee](https://img.buymeacoffee.com/button-api/?text=Buy%20me%20a%20coffee&emoji=☕&slug=neiderruiz&button_colour=FFDD00&font_colour=000000&font_family=Cookie&outline_colour=000000&coffee_colour=ffffff)](https://www.buymeacoffee.com/neiderruiz)