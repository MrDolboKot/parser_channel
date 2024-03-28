<?php
	include('db.php');

	function autorize($login,$pwd,$type = 0)
	{
		global $db;
		if($type == 1){ $pwd = md5($pwd); }
		$s = "SELECT `id` FROM `Accounts` WHERE `login` = '$login' AND `password` = '$pwd'";
		$r = mysqli_query($db,$s);
		$ok = mysqli_fetch_assoc($r);
		if($ok['id'] > 0)
		{	
			$_SESSION['user_id'] = $ok['id'];
			$_SESSION['login'] = $login;
			$_SESSION['hash'] = $pwd;
			$self_account_id = get_setting('self_account_id');
			$_SESSION['self_account_id'] = $self_account_id;
			setcookie('SameSite','None');
			return true;
		} else { return false; }	
	}

	
	function get_title($title)
	{
		global $db;
		$s = "SELECT `name` FROM `a_menu` WHERE `alias` = '$title'";
		$r = mysqli_query($db, $s);
		$ok = mysqli_fetch_assoc($r);
		$num = mysqli_num_rows($r);
		if($num > 0){ return $ok['name']; } 
		
	}
	
	function get_setting($col)
	{
		global $db;
		$r = mysqli_query($db,"SELECT `$col` FROM `setting` WHERE `id` = '1'");
		$ok = mysqli_fetch_assoc($r);
		return $ok[$col];
	}
	
	function get_setting_account($col,$id)
	{
		global $db;
		$r = mysqli_query($db,"SELECT `$col` FROM `accounts_setting` WHERE `id` = '$id'");
		$num = mysqli_num_rows($r);
		if($num == 0){ return 0; }
		$ok = mysqli_fetch_assoc($r);
		return $ok[$col];
	}
	
	function get_setting_channel($col,$id_setting)
	{
		global $db;
		$r = mysqli_query($db,"SELECT `$col` FROM `channels_settings` WHERE `id` = '$id_setting'");
		$ok = mysqli_fetch_assoc($r);
		return $ok[$col];
	}
	
	
	function unicode_html($str)
	{
		$entity = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', function ($m) {
		$char = current($m);
		$utf = iconv('UTF-8', 'UCS-4', $char);
		return sprintf("&#x%s;", ltrim(strtoupper(bin2hex($utf)), "0"));
		}, $str);
		return $entity;
	}
	
	function unicode_char($str)
	{
		if(strstr($str,'&#x'))
		{
			$ok = mb_convert_encoding($str, 'UTF-8', 'HTML-ENTITIES');
			return $ok;
		} else { return $str; }
	}
	
	

	function translit($s)
	{
	  $s = (string) $s;
	  $s = strip_tags($s);
	  $s = str_replace(array("\n", "\r"), " ", $s); 
	  $s = preg_replace("/\s+/", ' ', $s);
	  $s = trim($s);
	  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
	  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
	  $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s);
	  $s = str_replace(" ", "_", $s);
	  return $s; 
	}


	function get_link($i)
	{
		$tmp = tempnam(sys_get_temp_dir(), 'php');
		file_put_contents($tmp, file_get_contents($i));

		$data = array(
			  'uploaded_photo' => new CURLFile($tmp),
			);
		
		$evo = curl_init(); 
		curl_setopt($evo,CURLOPT_HTTPHEADER,array('Accept:application/json, text/javascript, */*; q=0.01',));
		curl_setopt($evo, CURLOPT_URL, 'https://telegra.ph/upload');	
		curl_setopt($evo, CURLOPT_POST, true);
		curl_setopt($evo, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.75 Safari/537.36');
		curl_setopt($evo, CURLOPT_POSTFIELDS,($data));
		curl_setopt($evo, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($evo);
		$json = json_decode($json,true);		
		curl_close($evo);
		$url = 'https://telegra.ph'.$json[0]['src'];
		return $url;

	}
				


	function send_vote($channel,$message,$file,$channel_id,$file_id = 0)
	{
		global $db;
		global $api_bot_url;

		$buttons_inline['inline_keyboard'][0][0]['text'] = '👍 0';
		$buttons_inline['inline_keyboard'][0][0]['callback_data'] = 'rating:+';
		$buttons_inline['inline_keyboard'][0][1]['text'] = '👎 0';
		$buttons_inline['inline_keyboard'][0][1]['callback_data'] = 'rating:-';
			
		$markup = json_encode($buttons_inline);
		$message = htmlspecialchars_decode($message);
		if(empty($file_id)){ $object = new CURLFile(realpath($file)); } else { $object = $file_id; }
		$data_post = array(
				'chat_id'=> $channel,
				'caption'=>$message,
				'reply_markup' => $markup,
				'parse_mode'=>'HTML',
				'photo'=> $object,
		);

		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type:multipart/form-data',));
		curl_setopt($ch, CURLOPT_URL, $api_bot_url.'/sendPhoto?');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS,($data_post));
		$json_ = curl_exec($ch);
		$json = json_decode($json_,true);
		if(!isset($json['result'])){ return false; }

		if(empty($file_id)){ $file_id = $json['result']['photo'][0]['file_id']; }
		$message_id  = $json['result']['message_id'];
		
		if($message_id > 0)
		{
			$message = unicode_html($message);
			mysqli_query($db,"INSERT INTO `vote_channels` (`channel_id`,`message`,`message_id`,`rating`,`plus`,`minus`) 
																VALUES ('$channel_id','$message','$message_id',0,0,0)");
			$id_post = mysqli_insert_id($db);
			
			$buttons_inline['inline_keyboard'][0][0]['text'] = '👍 0';
			$buttons_inline['inline_keyboard'][0][0]['callback_data'] = "/rating:0:0:+:$message_id:$id_post";
			$buttons_inline['inline_keyboard'][0][1]['text'] = '👎 0';
			$buttons_inline['inline_keyboard'][0][1]['callback_data'] = "/rating:0:0:-:$message_id:$id_post";
		 
			$markup = json_encode($buttons_inline);
			
			$content = array(
					'chat_id' => $channel,
					'message_id'=>$message_id,
					'reply_markup' => $markup,
					'caption' => $message,
					'parse_mode'=>'HTML',
					'disable_notification'=>false,
				);
	
			$data = file_get_contents($api_bot_url.'/editMessageCaption?'.http_build_query($content));
			return $file_id;	
		}else { return 0; }
		
	}
	
	
	function query_x($url)
	{
		$evo = curl_init(); 
		curl_setopt($evo, CURLOPT_URL, $url);
		curl_setopt($evo, CURLOPT_HEADER, false);
		curl_setopt($evo, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($evo, CURLOPT_BINARYTRANSFER,1);
		curl_setopt($evo,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($evo, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($evo, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($evo, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)');
		curl_exec($evo);
	}
	
	function kill_all_process($account_id)
	{
		if($account_id < 1){ return; }
		$list = shell_exec("ps auxw | grep 'php forward_bot.php $account_id' | grep -v grep");
		$list = explode("\n",$list);
		$list = array_filter($list);
		$list = array_values($list);
		chdir($_SERVER['DOCUMENT_ROOT']);
		
		foreach($list as $num=>$item)
		{
			$arr = explode(" ",$item);
			foreach($arr as $val)
			{
				$val = intval($val);
				if($val > 0){ $pid = $val;break; } 
			
			}
			if($pid < 1){ return; }
			exec("kill -9 $pid");
		}
	}


	function message_replace($val_x,$message)
	{
		$replace_x = '';
		if(mb_stristr($val_x,'|'))
		{ 
			list($val_x,$replace_x) = explode('|',$val_x); 
		}
		
		$filt = explode(' ',$val_x);
		$word_rm = $filt[0];
		if(empty($word_rm)){ return $message; }
		
		$message_pars = str_replace("\n",' ',$message);
		$arr = explode(' ',$message_pars);
		$index = array_search($word_rm,$arr);

		$word_replace = '';
		for($q=0;$q<count($filt); $q++)
		{
			$word_replace .=  $arr[$index].' ';
			$index++;
		}
		$word_replace = trim($word_replace);
		
		$message_new = mb_eregi_replace($word_replace,$replace_x,$message);
		$message_new = trim($message_new);
		if(empty($message_new)){ return $message; }
		
		return $message_new;
	}
	
	function to_download($file){
	  if (file_exists($file)){
		if (ob_get_level()) {
		  ob_end_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	  }
	}

?>