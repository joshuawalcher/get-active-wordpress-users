<?php
//init into WP globals
require('wp-blog-header.php');
//get the global WPDB
global $wpdb;
//get all users that have ever logged in
$sql = "SELECT user_id, meta_value from " . $wpdb->prefix . "usermeta where meta_key = 'session_tokens' ORDER by user_id ASC";
$sessions = $wpdb->get_results($sql);
//init the CSV
$data = '';

foreach ($sessions as $session) {
	//init the variables on every loop
  	$first = '';
	$last = '';
	$userId = '';
	$lastLogin = '';
	$email = '';
  	
	//the meta_value is a serialized array within another array that has a unique sessionid.
 	 //Anyone know a way to bypass that key without a foreach? Sigh...
	$code = unserialize($session->meta_value);
	foreach($code as $array){
		//got the login timestamp
    		$lastLogin = $array['login'];
	}
	$userId = (int)$session->user_id;
	
  	//really, this may be all you need - the user id. But it might be useful to get the names and emails too.
  
  	//get the first and last name
  	$pull = $wpdb->get_results("SELECT meta_key, meta_value FROM " . $wpdb->prefix . "usermeta WHERE meta_key IN ('first_name','last_name') AND user_id = '$userId'");
	foreach($pull as $row){
		if($row->meta_key == 'first_name'){
			$first = $row->meta_value;
		} else {
			$last = $row->meta_value;
		}
	}
  
  	//get the email.
	$pull = $wpdb->get_results("SELECT user_email FROM " . $wpdb->prefix . "users where ID = '$userId'");
	foreach($pull as $row){
		$email = $row->user_email;
	}
  
	//add a new row to the CSV
	$data .= "$userId,$first,$last,$email," . date('m/d/Y',$lastLogin) . "\n";
	
}

//create the file
$fname = 'wordpress-users-and-last-login.csv';
$fp = fopen($fname,'w');
fwrite($fp,$data);
fclose($fp);

//attempt to stream to browser. Is failing for me in Chrome.
header('Content-type: application/csv');
header("Content-Disposition: inline; filename=".$fname);
readfile($fname);
