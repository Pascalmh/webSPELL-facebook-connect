Facebook Connect for webSPELL 4.2.*
=========================

## Requirements
- Working [webSPELL 4.2.*](installation https://github.com/webSPELL/)
- [Facebook PHP SDK](https://github.com/facebook/facebook-php-sdk)

## Install

- Upload _facebook.php into your root webSPELL-Directory
- Put the src-Folder of the Facebook PHP SDK into a Folder named "facebook", then upload it into the root webSPELL-Directory aswell.
- Add the row "fbID VARCHAR(255)" to your PREFIX_user table 
- Add the [Facebook Login Button](https://developers.facebook.com/docs/plugins/login-button)

### Modify _facebook.php
- Fill in the *appId* and *secret* on lines 4/5 (you need to create a new App to get this data https://developers.facebook.com/apps)
