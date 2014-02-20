<?php
/*
##########################################################################
#                                                                        #
#           Version 4       /                        /   /               #
#          -----------__---/__---__------__----__---/---/-               #
#           | /| /  /___) /   ) (_ `   /   ) /___) /   /                 #
#          _|/_|/__(___ _(___/_(__)___/___/_(___ _/___/___               #
#                       Free Content / Management System                 #
#                                   /                                    #
#                                                                        #
#                                                                        #
#   Copyright 2005-2011 by webspell.org                                  #
#                                                                        #
#   visit webSPELL.org, webspell.info to get webSPELL for free           #
#   - Script runs under the GNU GENERAL PUBLIC LICENSE                   #
#   - It's NOT allowed to remove this copyright-tag                      #
#   -- http://www.fsf.org/licensing/licenses/gpl.html                    #
#                                                                        #
#   Code based on WebSPELL Clanpackage (Michael Gruber - webspell.at),   #
#   Far Development by Development Team - webspell.org                   #
#                                                                        #
#   visit webspell.org                                                   #
#                                                                        #
##########################################################################
*/

$_language->read_module('login');

if($loggedin) {
	$username='<a href="index.php?site=profile&amp;id='.$userID.'"><b>'.strip_tags(getnickname($userID)).'</b></a>';
	if(isanyadmin($userID)) $admin='&#8226; <a href="admin/admincenter.php" target="_blank">'.$_language->module['admin'].'</a><br />';
	else $admin='';
	if(isclanmember($userID) or iscashadmin($userID)) $cashbox='&#8226; <a href="index.php?site=cash_box">'.$_language->module['cash-box'].'</a><br />';
	else $cashbox='';
	$anz=getnewmessages($userID);
	if($anz) {
		$newmessages=' (<b>'.$anz.'</b>)';
	}
	else $newmessages='';
	if($getavatar = getavatar($userID)) $l_avatar='<img src="images/avatars/'.$getavatar.'" alt="Avatar" />';
	else $l_avatar=$_language->module['n_a'];


	eval ("\$logged = \"".gettemplate("logged")."\";");
	echo $logged;
}
else {
    require_once('facebook-php-sdk/src/facebook.php');

    $facebook_appID = 'YOUR_APP_ID';
    $facebook_secret = 'YOUR_APP_SECRET';

    $facebook = new Facebook(array(
        'appId' => $facebook_appID,
        'secret' => $facebook_secret,
        'allowSignedRequest' => false // optional but should be set to false for non-canvas apps
    ));

    $user_id = $facebook->getUser();

    if($user_id) {

        // We have a user ID, so probably a logged in user.
        // If not, we'll get an exception, which we handle below.
        try {
            $ds=mysql_fetch_array(safe_query("SELECT userID, password, lastlogin FROM ".PREFIX."user WHERE fbID = '".$user_id."' "));
            if(mysqli_num_rows($ds)) {
                // We have a Facebook User that is already registered on our webSPELL Site, lets log him in

                // copied from github.com/webSPELL/webSPELL-4.2.3/blob/master/checklogin.php#L69-92
                $_SESSION['ws_auth'] = $ds['userID'].":".$ds['password'];
                $_SESSION['ws_lastlogin'] = $ds['lastlogin'];
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
                //remove sessiontest variable
                if(isset($_SESSION['ws_sessiontest'])) unset($_SESSION['ws_sessiontest']);
                //cookie
                $cookieName = "ws_auth";
                $cookieValue = $ds['userID'].":".$ds['password'];
                $cookieExpire = time()+($sessionduration*60*60);
                if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
                    $cookieInfo = session_get_cookie_params();
                    setcookie($cookieName,$cookieValue,$cookieExpire,$cookieInfo['path'],$cookieInfo['domain'],$cookieInfo['secure'],true);
                }
                else{
                    setcookie($cookieName,$cookieValue,$cookieExpire);
                }
                unset($cookieName);
                unset($cookieValue);
                unset($cookieExpire);
                unset($cookieInfo);
                //Delete visitor with same IP from whoisonline
                safe_query("DELETE FROM ".PREFIX."whoisonline WHERE ip='".$GLOBALS['ip']."'");
                //Delete IP from failed logins
                safe_query("DELETE FROM ".PREFIX."failed_login_attempts WHERE ip = '".$GLOBALS['ip']."'");

            }
            else {
                // We have a Facebook User that is not yet registered on our webSPELL Site, lets sign him up

                $registerdate=time();
                $mail = $user_profile['email'];
                $md5pwd = md5(stripslashes(RandPass(6)));

                safe_query("INSERT INTO `".PREFIX."user` (`registerdate`, `lastlogin`, `username`, `password`, `nickname`, `email`, `activated`,`ip`) VALUES ('$registerdate', '$registerdate', '$username', '$md5pwd', '$nickname', '$mail', '1', '".$GLOBALS['ip']."')");

                $insertid = mysql_insert_id();

                // insert in user_groups
                safe_query("INSERT INTO ".PREFIX."user_groups ( userID ) values('$insertid' )");
            }

        } catch(FacebookApiException $e) {
            error_log($e->getType());
            error_log($e->getMessage());
        }
    }

    //set sessiontest variable (checks if session works correctly)
    $_SESSION['ws_sessiontest'] = true;
    eval ("\$loginform = \"".gettemplate("login")."\";");
    echo $loginform;
}

?>