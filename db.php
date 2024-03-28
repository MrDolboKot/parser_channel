<?php
	ini_set('session.gc_maxlifetime', 2592000);
	ini_set('session.cookie_lifetime', 2592000);
	session_start();
	date_default_timezone_set('Europe/Moscow');
	set_time_limit(0);

	DEFINE('login','XXXXXXXXXX');
	DEFINE('pass','XXXXXXXXXX');
	DEFINE('host','localhost');
	DEFINE('base','XXXXXXXXXX');
	DEFINE('dir','pages_x/');
	DEFINE('root_dir','/parser_channel');
	DEFINE('download_dir',root_dir.'/files');

	$db = mysqli_connect(host,login,pass,base);
 	mysqli_query($db,'SET NAMES utf8');
	mysqli_query($db,'SET COLLATION_CONNECTION=utf8_bin');

	mysqli_select_db($db,base);
	mysqli_query($db,'set global sql_mode="NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"');
	global $db;

	$ssl = 'http://'; 
	global $ssl;
	
	
	if(isset($_SESSION['user_id']))
	{ 
		$user_id = $_SESSION['user_id'];
		$login_ = $_SESSION['login'];
		$pwd_ = $_SESSION['hash'];

		$session = autorize($login_,$pwd_); 
	}

?>
