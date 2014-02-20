<?php

require_once("facebook.php");

$config = array(
    'appId' => 'YOUR_APP_ID',
    'secret' => 'YOUR_APP_SECRET',
    'fileUpload' => false, // optional
    'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
);
 
// Get User ID
$user_id = $facebook->getUser();

if($user_id) {
    try {
        $user_profile = $facebook->api('/me','GET');
    } catch(FacebookApiException $e) {
        // If the user is logged out, you can have a
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        $login_url = $facebook->getLoginUrl();
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
    }

}
else {

}

if ($user_id) {
  $next = 'http://'.$hp_url.'/logout.php';
  $logoutUrl = $facebook->getLogoutUrl(array('next' => $next));
} else {
  $loginUrl = $facebook->getLoginUrl(array('req_perms' => 'email,user_birthday,user_website'));;
}

if(!isset($uid)) $uid = $user_profile['id'];

$ds=mysql_fetch_array(safe_query("SELECT * FROM ".PREFIX."user WHERE fbID = '".$uid."' "));
if($user_id && $uid!=$ds['fbID']) {
 $registerdate = time();           
 $firstName   = $user_profile['first_name'];
 $lastName    = $user_profile['last_name'];
 $fullname    = $user_profile['first_name'].''.$me['last_name'];
 $nickname    = preg_replace( '/[^a-z0-9-]/i', '', $fullname );
 $homepage    = $user_profile['website'];
 $birthday    = $user_profile['birthday'];
 $fbID        = $user_profile['id'];
 $bdaypieces  = explode("/",$user_profile['birthday']);
 $correctbday = $bdaypieces[2].'-'.$bdaypieces[0].'-'.$bdaypieces[1].' 00:00:00';  
 $country     = substr($user_profile['locale'], 0, 2);
 $sex         = $user_profile['gender']; 
 if($sex=="male")       $sex = "m";
 elseif($sex=="female") $sex = "f";
 else                   $sex = "u";
 $mail        = $user_profile['email'];
 
 // check nickname inuse
 $ergebnis = safe_query("SELECT * FROM ".PREFIX."user WHERE nickname = '$nickname' ");
 $num = mysql_num_rows($ergebnis);
 if($num) $nickname .= substr($registerdate, -3, 4);
 
 $newpwd=RandPass(10);
 $newmd5pwd=md5($newpwd);
 
 safe_query("INSERT INTO `".PREFIX."user` (`registerdate`,
  `lastlogin`,
   `username`,
    `password`,
     `nickname`,
      `email`,
       `newsletter`,
        `activated`,
         `firstname`,
          `lastname`,
           `homepage`,
            `fbID`,
             `birthday`,
              `sex`,
               `country`,
                `language`) VALUES ('$registerdate',
  '$registerdate',
   '$nickname',
    '$newmd5pwd',
     '$nickname',
      '$mail',
       '0',
        '1',
         '$firstName',
          '$lastName',
           '$homepage',
            '$fbID',
             '$correctbday',
              '$sex',
               '$country',
                '$country')");  
                   
  $insertid = mysql_insert_id();
	// insert in user_groups
	safe_query("INSERT INTO ".PREFIX."user_groups ( userID ) values('$insertid' )");
}
 
if(!$loggedin && $uid!="") {
$result = safe_query("SELECT * FROM ".PREFIX."user WHERE fbID='".$user_profile['id']."'");
$ds=mysql_fetch_array($result); 
       
  if($result && $ds['userID']!=0) {
  // User is loggedin with Facebook but not into webSPELL, lets log him in!
     
          //session
					$_SESSION['ws_auth'] = $ds['userID'].":".$ds['password'];
					$_SESSION['ws_lastlogin'] = $ds['lastlogin'];
					$_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
					//remove sessiontest variable
					if(isset($_SESSION['ws_sessiontest'])) unset($_SESSION['ws_sessiontest']);
					//cookie
					setcookie("ws_auth", $ds['userID'].":".$ds['password'], time()+($sessionduration*60*60));					
					//Delete visitor with same IP from whoisonline
					safe_query("DELETE FROM ".PREFIX."whoisonline WHERE ip='".$GLOBALS['ip']."'");
					//Delete IP from failed logins
					safe_query("DELETE FROM ".PREFIX."failed_login_attempts WHERE ip = '".$GLOBALS['ip']."'");
					$login = 1; 
					
					$insertid = mysql_insert_id();

		      // insert in user_groups
		      safe_query("INSERT INTO ".PREFIX."user_groups ( userID ) values('$insertid' )");
           
         header("Location: index.php?site=loginoverview");
         echo '<meta http-equiv="refresh" content="0" />';
  }
}
?>