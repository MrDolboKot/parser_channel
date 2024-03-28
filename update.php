<?php
	include('functions.php');
	
	mysqli_query($db,"ALTER TABLE `accounts_setting` ADD `last_time` TEXT NOT NULL");
	mysqli_query($db,"ALTER TABLE `accounts_setting` ADD `restart` INT NOT NULL");
	mysqli_query($db,"ALTER TABLE `join_exists` ADD `ready` INT NOT NULL");
	echo "<h2>UPDATE BASE SUCCESS v4.3 in 4.4 KostolomVers</h2><br><b>Detele file: update.php</b>";
?>