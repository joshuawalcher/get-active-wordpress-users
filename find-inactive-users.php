<?php
//init into WP globals
require('wp-blog-header.php');
//get the global WPDB
global $wpdb;
//get all users that have NEVER logged in
$sql = "SELECT u.ID from " . $wpdb->prefix . "users u where (SELECT COUNT(*) FROM " . $wpdb->prefix . "usermeta um where um.meta_key = 'session_tokens' AND um.user_id = u.ID) = 0";
$inactives = $wpdb->get_results($sql);
//init the CSV
$data = '';
foreach ($inactives as $user) {
	//init the variables on every loop
  $first = '';
	$last = '';
	$userId = '';
	$email = '';
	$userId = (int)$user->ID;
	
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
$fname = 'inactive-wordpress-users.csv';
$fp = fopen($fname,'w');
fwrite($fp,$data);
fclose($fp);
//attempt to stream to browser. Is failing for me in Chrome.
header('Content-type: application/csv');
header("Content-Disposition: inline; filename=".$fname);
readfile($fname);
