<?php 
	include('functions.php'); 

	$id_account = intval($argv[1]);
	if($id_account < 1){ exit; }
	
	global $db;
	require_once 'vendor/autoload.php';
	
	$path = dirname(dirname(__FILE__));
	$token = get_setting_account('bot_token',$id_account);
	$api_bot_url = "https://api.telegram.org/bot".$token;
	
	$status = get_setting_account('status',$id_account);
	if($status == 1){ echo "stoppend\n";exit; }

	$num_grouped = 0;
	$singleMedia = array();
	$grouped_old = 0;
	$message_list = array();
	$send_ping = 0;
	$album_info = 0;
	include('async_bot.php');

?>