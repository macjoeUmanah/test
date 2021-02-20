<?php
    error_reporting(E_ALL ^ (E_STRICT | E_DEPRECATED | E_NOTICE | E_WARNING));

	$con = mysqli_connect("localhost","tellucle_sms",'%t6$NLSmUCSE');
	
    if (!$con){
	   die('Could not connect: ' . mysqli_error());
	}

    mysqli_select_db($con,"tellucle_smsdashboard"); 

	//fetch data from configs	   
    $configs = mysqli_query($con,"SELECT * FROM configs"); 
	$configdata = mysqli_fetch_array($configs); 

	$dbconfigs = mysqli_query($con,"SELECT * FROM dbconfigs");
	$dbconfigsdata = mysqli_fetch_array($dbconfigs);

	define('SITEURL',$configdata['site_url']);
	define('NEXMO_KEY',$configdata['nexmo_key']);
	define('NEXMO_SECRET',$configdata['nexmo_secret']);

	define('TWILIO_ACCOUNTSID',$configdata['twilio_accountSid']);
	define('TWILIO_AUTH_TOKEN',$configdata['twilio_auth_token']);

    define('PLIVO_KEY',$configdata['plivo_key']);
	define('PLIVO_TOKEN',$configdata['plivo_token']);
    define('PLIVOAPP_ID',$configdata['plivoapp_id']);

	define('DB_USERNAME',$dbconfigsdata['dbusername']);
	define('DB_NAME',$dbconfigsdata['dbname']);
	define('DB_PASSWORD',$dbconfigsdata['dbpassword']);
	  

?>