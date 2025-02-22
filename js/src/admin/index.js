import app from 'flarum/admin/app';

app.initializers.add('@neiderruiz/flarum-simple-share-session', () => {
    app.extensionData.for('neiderruiz-simple-share-session').registerSetting({
        setting: 'neiderruiz_fsss.verify_session_url',
        type: 'url',
        label: "URL of the API to verify the session",
        help: "The API should return a JSON with the user data",
        placeholder: "https://yourdomain.com/api/verify-session"
    });
});
