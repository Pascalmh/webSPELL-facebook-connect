<?php

 require 'facebook/src/facebook.php';
$facebook = new Facebook(array(
  'appId'  => '',
  'secret' => '',
)); 
 
// Get User ID
$user = $facebook->getUser(); 
 
if ($user) {
  try {
    // Proceed knowing you have a logged in user who's authenticated.
    $user_profile = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    error_log($e);
    $user = null;
  }
}

if ($user) {
  $next = 'http://'.$hp_url.'/logout.php';
  $logoutUrl = $facebook->getLogoutUrl(array('next' => $next));
} else {
  $loginUrl = $facebook->getLoginUrl(array('req_perms' => 'email,user_birthday,user_website'));;
}

if(!isset($uid)) $uid = $user_profile['id'];

$ds=mysql_fetch_array(safe_query("SELECT * FROM ".PREFIX."user WHERE fbID = '".$uid."' "));
if($user && $uid!=$ds['fbID']) {
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
	// Send Email with Password to User
  $message = 'Hi '.$firstName.' '.$lastName.'
  Welcome to '.$myclanname.'
  You can also LogIn with this Information:
  Username: '.$nickname.'
  Password: '.$newpwd.'
  
  You will not need it unless you can not LogIn with your Facebook Account.
  
  have fun on our page
  http://'.$hp_url.'/';
  mail($mail,'New Account on '.$myclanname.'', $message, "From:".$admin_email."\nContent-type: text/plain; charset=utf-8\n");
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