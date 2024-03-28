<?php
	include('functions.php');

	$path = dirname(__FILE__);
	exec("sudo chmod -R 777 $path/accounts");
	exec("sudo chown -R www-data:www-data $path/accounts");
	chdir($path);
	
	$self = 'forward_bot.php';
	$r = mysqli_query($db,"SELECT `id`,`profile_tg`,`restart`,`last_time` FROM `accounts_setting` WHERE `status` = '0'");
	while($list = mysqli_fetch_assoc($r))
	{
		$id_account = $list['id'];
		$min = $list['restart'];
		$time = $list['last_time'];
		if($time < 1)
		{ 
			$time = time();
			mysqli_query($db,"UPDATE `accounts_setting` SET `last_time` = '$time' WHERE `id` = '$id_account'");
		}
		
		if($min > 0)
		{
			$min_x = time() - $time;
			$min_x = round($min_x / 60);
	
			if($min_x >= $min)
			{
				mysqli_query($db,"UPDATE `accounts_setting` SET `last_time` = '".time()."' WHERE `id` = '$id_account'");
				kill_all_process($id_account);
				chdir($path);
				sleep(1);
			}
		}
		
		$rec = shell_exec("ps auxw | grep 'php $self $id_account' | grep -v grep");
		$rec = explode("\n",$rec);
		$rec = array_filter($rec);
		$all = count($rec);	
	
		if($all > 0){ continue; }
		exec("php $self $id_account > /dev/null &");
	}
?>