Facebook Connect for webSPELL 4.2.*
=========================

## Requirements
- Working installation of [webSPELL 4.2.*](https://github.com/webSPELL/)
- [Facebook PHP SDK](https://github.com/facebook/facebook-php-sdk)
- Your own [Facebook App](https://developers.facebook.com/apps)

## Install

- Upload `login.php` and the `facebook-php-sdk` folder into the root webSPELL-Directory

Your webSPELL-Directory should now contain this files and folders (you won't need the other facebook-php-sdk files and folders)
``` php
facebook-php-sdk/src/base_facebook.php
facebook-php-sdk/src/facebook.php
facebook-php-sdk/src/fb_ca_chain_bundle.crt
index.php // this being your webSPELL index.php
login.php
```

- Add the row "fbID VARCHAR(255)" to your PREFIX_user table
- Open `_facebook.php` and search for `YOUR_APP_ID` and `YOUR_APP_SECRET` fill in the information from your [Facebook App](https://developers.facebook.com/apps)
- Open `templates/login.html` and add `<a href="$facebook_login">Login with Facebook</a>`
- Open `templates/logged.html` and change the Logout Link to `<a href="$facebook_logout">%logout%</a>`

## Customization

You might want to add an [Facebook Login Buttons](https://developers.facebook.com/docs/facebook-login/checklist/#brandedlogin) into your templates/login.html