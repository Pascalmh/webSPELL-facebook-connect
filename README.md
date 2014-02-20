Facebook Connect for webSPELL 4.2.*
=========================

## Requirements
- Working installation of [webSPELL 4.2.*](https://github.com/webSPELL/)
- [Facebook PHP SDK](https://github.com/facebook/facebook-php-sdk)
- Your own [Facebook App](https://developers.facebook.com/apps)

## Install

- Upload `login.php` and `templates/login.html` and the `facebook-php-sdk` folder into the root webSPELL-Directory

Your webSPELL-Directory should now contain this files and folders (you won't need the other facebook-php-sdk files and folders)
```
facebook-php-sdk/src/base_facebook.php
facebook-php-sdk/src/facebook.php
facebook-php-sdk/src/fb_ca_chain_bundle.crt
templates/login.html
index.php // this being your webSPELL index.php
login.php
```

- Add the row "fbID VARCHAR(255)" to your PREFIX_user table
- Open login.php and search for `YOUR_APP_ID` and `YOUR_APP_SECRET` fill in the information from your [Facebook App](https://developers.facebook.com/apps)
