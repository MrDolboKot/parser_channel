<?php
	header('Content-Type: text/html; charset=utf-8');
	include('functions.php');
	
	if(!$session){ exit; }
	if(isset($_POST['act'])){ $act = strip_tags($_POST['act']); }
	if(isset($_POST['id'])){ $id = intval($_POST['id']); }
	preg_match("/^[a-zA-Z0-9\w]+/", $act,$actx);
	if(isset($actx[0])){ $act = $actx[0]; }
	

	if($act == 'change_pwd')
	{
		$pwd = strip_tags($_POST['pwd']);
		
		$hash = md5($pwd);
		mysqli_query($db,"UPDATE `Accounts` SET `password` = '$hash' WHERE `id` = '$user_id'");
		echo json_encode(array('stat'=>1));
	}
	
	
	if($act == 'save_setting_channel')
	{
		$account_id = intval($_POST['account_id']);
		$spam_filter = unicode_html($_POST['spam_filter']);
		$replace_words = unicode_html($_POST['replace_words']);
		$filter_message = intval($_POST['filter_message']);
		$my_message = htmlspecialchars($_POST['my_message']);
		$enable_my_message = intval($_POST['enable_my_message']);
		$filter_inline = intval($_POST['filter_inline']);
		$replace_link = strip_tags($_POST['replace_link']);
		$enable_bot_vote = intval($_POST['enable_bot_vote']);
		$filter_links = strip_tags($_POST['filter_links']);
		$skip_text = intval($_POST['skip_text']);
		$timetable = intval($_POST['timetable']);
		$time_post = strip_tags($_POST['time_post']);
		$limit_post = intval($_POST['limit_count_post']);
		$limit_hours = intval($_POST['limit_hours']);
		$limit_status = intval($_POST['limit_status']);
		$forward_message = intval($_POST['forward_message']);
		$ignore_post_type = intval($_POST['ignore_post_type']);
		$sugn_channel = unicode_html($_POST['sugn_channel']);
		$word_send_post_type = intval($_POST['word_send_post_type']);
		$word_send_post_func = intval($_POST['word_send_post_func']);
		$word_send_post = unicode_html($_POST['word_send_post']);
		$replace_username = strip_tags($_POST['replace_username']);
		$replace_username_stat = intval($_POST['replace_username_stat']);
		$update_message = intval($_POST['update_message']);
		$delete_message = intval($_POST['delete_message']);
		$reply_post = intval($_POST['reply_post']);
		$length_message_limit = intval($_POST['length_message_limit']);
		$length_message = intval($_POST['length_message']);
		$type_check_m = intval($_POST['type_check_m']);
		$my_message = unicode_html($my_message);
		$schedule = intval($_POST['schedule']);
		$schedule_date = strip_tags($_POST['schedule_date']);
		$opt_save = intval($_POST['opt_save']);
		$send_text_no_media = intval($_POST['send_text_no_media']);
		$post_no_webpage = intval($_POST['post_no_webpage']);
		$ignore_album = intval($_POST['ignore_album']);
		$ignore_audio = intval($_POST['ignore_audio']);
		$duplicat_dis = intval($_POST['duplicat_dis']);
		
		if($opt_save == 0)
		{
			mysqli_query($db,"UPDATE `channels_settings` SET `spam_filter` = '$spam_filter', `replace_words` = '$replace_words',
							`filter_message` = '$filter_message', `my_text_message` = '$my_message', 
							`enable_text_message` = '$enable_my_message', `filter_inline` = '$filter_inline',
							`enable_bot_vote` = '$enable_bot_vote',`replace_link` = '$replace_link',`filter_links` = '$filter_links',
							`skip_text` = '$skip_text',`time_post` = '$time_post',`timetable` = '$timetable',
							`limit_post` = '$limit_post',`limit_hours` = '$limit_hours', `limit_status` = '$limit_status',
							`forward_message` = '$forward_message',`ignore_post_type` = '$ignore_post_type', `sugn_channel` = '$sugn_channel', 
							`word_send_post_type` = '$word_send_post_type', `word_send_post_func` = '$word_send_post_func',
							`word_send_post` = '$word_send_post', `replace_username` = '$replace_username', 
							`replace_username_stat` = '$replace_username_stat', `update_message` = '$update_message',
							`delete_message` = '$delete_message',`reply_post` = '$reply_post',`length_message_limit` = '$length_message_limit', 
							 `length_message` = '$length_message', `type_check_m` = '$type_check_m', `schedule` = '$schedule',
							 `schedule_date` = '$schedule_date',`send_text_no_media` = '$send_text_no_media',
							 `post_no_webpage` = '$post_no_webpage',`ignore_album` = '$ignore_album', `ignore_audio` = '$ignore_audio',
							 `duplicat_dis` = '$duplicat_dis' WHERE `id` = '$id'");
		}else
		{
			$r = mysqli_query($db,"SELECT `setting_id` FROM `channels_origin` WHERE `account_id` = '$account_id'");
			while($list = mysqli_fetch_assoc($r))
			{
				$opt_id = $list['setting_id'];
				
				mysqli_query($db,"UPDATE `channels_settings` SET `spam_filter` = '$spam_filter', `replace_words` = '$replace_words',
								`filter_message` = '$filter_message', `my_text_message` = '$my_message', 
								`enable_text_message` = '$enable_my_message', `filter_inline` = '$filter_inline',
								`enable_bot_vote` = '$enable_bot_vote',`replace_link` = '$replace_link',`filter_links` = '$filter_links',
								`skip_text` = '$skip_text',`time_post` = '$time_post',`timetable` = '$timetable',
								`limit_post` = '$limit_post',`limit_hours` = '$limit_hours', `limit_status` = '$limit_status',
								`forward_message` = '$forward_message',`ignore_post_type` = '$ignore_post_type', `sugn_channel` = '$sugn_channel', 
								`word_send_post_type` = '$word_send_post_type', `word_send_post_func` = '$word_send_post_func',
								`word_send_post` = '$word_send_post', `replace_username` = '$replace_username', 
								`replace_username_stat` = '$replace_username_stat', `update_message` = '$update_message',
								`delete_message` = '$delete_message',`reply_post` = '$reply_post',`length_message_limit` = '$length_message_limit', 
								 `length_message` = '$length_message', `type_check_m` = '$type_check_m', `schedule` = '$schedule',
								 `schedule_date` = '$schedule_date',`send_text_no_media` = '$send_text_no_media',
								 `post_no_webpage` = '$post_no_webpage',`ignore_album` = '$ignore_album', `ignore_audio` = '$ignore_audio',
								 `duplicat_dis` = '$duplicat_dis' WHERE `id` = '$opt_id'");
			}
		}
		echo json_encode(array('stat'=>1));
	}
	
	if($act == 'save_setting')
	{
		$status = intval($_POST['status']);
		$bot_token = strip_tags($_POST['bot_token']);
		$self_account = intval($_POST['self_account']);
		$restart = intval($_POST['restart']);
		
		if(!empty($bot_token))
		{
			$url = "https://".$_SERVER['SERVER_NAME'].root_dir."/hook_vote.php";
			$u = "https://api.telegram.org/bot$bot_token/setWebhook?max_connections=$max&url=".$url;
			file_get_contents($u);
		}
		
		if(!empty($bot_token))
		{
			$res = file_get_contents("https://api.telegram.org/bot".$bot_token."/getme");
			$json = json_decode($res);
			if((!empty($json)) && ($json->ok == 1))
			{
				$username = $json->result->username;
				mysqli_query($db,"UPDATE `accounts_setting` SET `bot_username` = '$username' WHERE `id` = '$self_account'");
			}
		}
		
		$my_message = unicode_html($my_message);
		if(empty($username)){ $username = '<span style="color:red">[Укажите token бота!]</span>'; } else { $username = '@'.$username; }
	
		if($status == 1){ kill_all_process($_SESSION['self_account_id']); }
		
		mysqli_query($db,"UPDATE `accounts_setting` SET `status` = '$status',  `bot_token` = '$bot_token', `restart` = '$restart' 
																								WHERE `id` = '$self_account'");
		echo json_encode(array('stat'=>1,'username'=>$username));
	}
	
	
	if($act == 'add_channel')
	{
		$channel_publish = strip_tags($_POST['channel_publish']);
		$channel_origin = strip_tags($_POST['channel_origin']);
	
		$self_account_id = $_SESSION['self_account_id'];
		if(empty($self_account_id))
		{
			$rx = mysqli_query($db,"SELECT `id` FROM `accounts_setting` LIMIT 0,1");
			$num = mysqli_num_rows($rx);
			if($num > 0)
			{
				$data_a = mysqli_fetch_assoc($rx);
				$self_account_id = $data_a['id'];
				$_SESSION['self_account_id'] = $self_account_id;
				mysqli_query($db,"UPDATE `setting` SET `self_account_id` = '$self_account_id' WHERE `id` = '1'");
			}else{ echo json_encode(array('stat'=>2,'error'=>"Не выбран аккаунт"));exit; }
		}
		
		$profile = get_setting_account('profile_tg',$self_account_id);
		$pname = $profile;
		$channel_origin_id_user = intval($channel_origin);
		
		$rx1 = mysqli_query($db,"SELECT `id`,`name`,`channel_id` FROM `join_exists` WHERE `channel_url` = '$channel_origin' 
										AND `profile_tg` = '$profile' AND `id_account` = '$self_account_id' AND `type` = '1'");
		$num1 = mysqli_num_rows($rx1);
		
		$rx2 = mysqli_query($db,"SELECT `id`,`name`,`channel_id` FROM `join_exists` WHERE `channel_url` = '$channel_publish' 
											AND `profile_tg` = '$profile' AND `id_account` = '$self_account_id' AND `type` = '2'"); 
		$num2 = mysqli_num_rows($rx2);
		if(($num1 > 0) && ($num2 > 0))
		{
			$res_origin = mysqli_fetch_assoc($rx1); 
			$channel_origin_id = $res_origin['channel_id'];
			$origin_name = $res_origin['name'];
			
			$res_publish = mysqli_fetch_assoc($rx2); 
			$channel_publish_id = $res_publish['channel_id'];
			$publish_name = $res_publish['name'];
		}else
		{

		require_once 'vendor/autoload.php';
		$settings['app_info']['api_id'] = 16282;
		$settings['app_info']['api_hash'] = 'ebfaa22710b9372b4679b8e81d5bf264';
		$settings['logger']['logger'] = 0;
		$settings['ipc']['slow'] = true;
		
		$profile = str_replace('+','',$profile);
		$profile = $_SERVER['DOCUMENT_ROOT'].root_dir.'/accounts/p_'.$profile;
		try
		{
			$MadelineProto = new \danog\MadelineProto\API($profile,$settings);
			$MadelineProto->async(true);
			$MadelineProto->loop(function () use ($MadelineProto){
			yield $MadelineProto->start();	

			global $db;
			global $channel_origin;
			global $channel_publish;
			global $channel_origin_id;
			global $origin_name;
			global $channel_publish_id;
			global $publish_name;
			global $pname;
			global $self_account_id;
			global $channel_origin_id_user;
	
			$channel_origin_id = 0;
			$channel_publish_id = 0;
			try
			{
				$rx = mysqli_query($db,"SELECT `id`,`name`,`channel_id` FROM `join_exists` WHERE `channel_url` = '$channel_origin' 
																				AND `profile_tg` = '$pname' AND `type` = '1'");
				$num1 = mysqli_num_rows($rx);
				if($num1 == 0)
				{
					try
					{
						if($channel_origin_id_user == 0)
						{ 
							yield $MadelineProto->channels->joinChannel(['channel' => $channel_origin, ]);
							$data_origin = yield $MadelineProto->channels->getFullChannel(['channel' => $channel_origin,]);
							$channel_origin_id = $data_origin['full_chat']['id'];
							$origin_name = unicode_html($data_origin['chats'][0]['title']);
						}else
						{	
							$data_origin = yield $MadelineProto->getFullInfo('-100'.$channel_origin_id_user);
							$channel_origin_id = $data_origin['Chat']['id'];
							$origin_name = unicode_html($data_origin['Chat']['title']);
						}
						
						mysqli_query($db,"INSERT INTO `join_exists` (`channel_url`,`profile_tg`,`type`,`name`,`channel_id`,`id_account`) 
											VALUES ('$channel_origin','$pname','1','$origin_name','$channel_origin_id','$self_account_id')");
					}catch(\danog\MadelineProto\RPCErrorException $e) 
					{  	
						$error = $e->getMessage();
						if($error == 'INVITE_HASH_INVALID'){ echo json_encode(array('stat'=>2,'error'=>$error));exit; }
						if(strstr($error,'FLOOD_WAIT_'))
						{ 
							list($n,$sec) = explode('WAIT_',$error);
							if($sec > 60){ $sec = round($sec / 60); $sec = $sec.' мин'; } else { $sec = $sec.' sec'; }
							echo json_encode(array('stat'=>2,'error'=>"Превышен лимит вступлений, немного подождите. $sec"));
							exit; 
						}elseif($error == 'USER_ALREADY_PARTICIPANT')
						{
							$data_origin = yield $MadelineProto->channels->getFullChannel(['channel' => $channel_origin,]);
							$channel_origin_id = $data_origin['full_chat']['id'];
							$origin_name = unicode_html($data_origin['chats'][0]['title']);
					
							mysqli_query($db,"INSERT INTO `join_exists` (`channel_url`,`profile_tg`,`type`,`name`,`channel_id`,`id_account`) 
												VALUES ('$channel_origin','$pname','1','$origin_name','$channel_origin_id','$self_account_id')");
						}
					}  
				}else
				{ 
					$res_origin = mysqli_fetch_assoc($rx); 
					$channel_origin_id = $res_origin['channel_id'];
					$origin_name = $res_origin['name'];
				}
				
				$rx = mysqli_query($db,"SELECT `id`,`name`,`channel_id` FROM `join_exists` WHERE `channel_url` = '$channel_publish' 
																					AND `profile_tg` = '$pname' AND `type` = '2'"); 
				$num2 = mysqli_num_rows($rx);
				if($num2 == 0)
				{
					try
					{
						yield $MadelineProto->channels->joinChannel(['channel' => $channel_publish, ]);
						
						$data_publish = yield $MadelineProto->channels->getFullChannel(['channel' => $channel_publish,]);
						$channel_publish_id = $data_publish['full_chat']['id'];
						$publish_name = unicode_html($data_publish['chats'][0]['title']);
	  					
						mysqli_query($db,"INSERT INTO `join_exists` (`channel_url`,`profile_tg`,`type`,`name`,`channel_id`,`id_account`) 
											VALUES ('$channel_publish','$pname','2','$publish_name','$channel_publish_id','$self_account_id')");
					}catch(\danog\MadelineProto\RPCErrorException $e) 
					{  
						$error = $e->getMessage();
						if($error == 'INVITE_HASH_INVALID'){ echo json_encode(array('stat'=>2,'error'=>$error));exit; }
						if(strstr($error,'FLOOD_WAIT_'))
						{ 
							list($n,$sec) = explode('WAIT_',$error);
							if($sec > 60){ $sec = round($sec / 60); $sec = $sec.' мин'; } else { $sec = $sec.' sec'; }
							echo json_encode(array('stat'=>2,'error'=>"Превышен лимит вступлений, немного подождите. $sec")); 
							exit; 
						}elseif($error == 'USER_ALREADY_PARTICIPANT')
						{
							$data_publish = yield $MadelineProto->channels->getFullChannel(['channel' => $channel_publish,]);
							$channel_publish_id = $data_publish['full_chat']['id'];
							$publish_name = unicode_html($data_publish['chats'][0]['title']);
						
							mysqli_query($db,"INSERT INTO `join_exists` (`channel_url`,`profile_tg`,`type`,`name`,`channel_id`,`id_account`) 
											VALUES ('$channel_publish','$pname','2','$publish_name','$channel_publish_id','$self_account_id')");
						}
					} 
				}else
				{ 
					$res_publish = mysqli_fetch_assoc($rx); 
					$channel_publish_id = $res_publish['channel_id'];
					$publish_name = $res_publish['name'];
				}
				
			}catch(\danog\MadelineProto\RPCErrorException $e) 
			{  	
				$error = $e->getMessage();
				if($error == 'INVITE_HASH_INVALID'){ echo json_encode(array('stat'=>2,'error'=>$error));exit; }
				elseif(strstr($error,'FLOOD_WAIT_'))
				{ 
					list($n,$sec) = explode('WAIT_',$error);
					echo json_encode(array('stat'=>2,'error'=>"Превышен лимит, немного подождите. $sec sec"));
					exit; 
				}

			} 
			
		});
			
		}catch(\danog\MadelineProto\RPCErrorException $e) { $error = $e->getMessage(); echo json_encode(array('stat'=>2,'error'=>$error));exit; } 
		}
		
		$time = time();
		mysqli_query($db,"INSERT INTO `channels_settings` (`status`, `spam_filter`, `replace_words`, `filter_links`, `filter_message`, 	
							`enable_text_message`, `my_text_message`, `filter_inline`, `replace_link`, `enable_bot_vote`, `skip_text`,`limit_time`) 
							VALUES (0, '@\nРеклама\nРекламе\nподписаться\nканал\nФулл залили\nПиши мне\nзакреп\nУдалим через\nУдалю через\nУдаляю через\nЧас и удаляем\nв телеграм\nпромокод\nставку\n http://bit.ly\n1xbet\n1bet\ncasino\nвпустит ещё\nНаписать\nПрогноз на матч\nв лс', '&#x41A;&#x430;&#x437;&#x438;&#x43D;&#x43E;\n@intimfact|@in4bo\nPOZOR|[LINE][LINE]<a href=\"https://t.me/in4bo\">&#x1F525; In4 Relax</a>\n&#x41A;&#x430;&#x43D;&#x430;&#x43B;|<b>&#x41A;&#x430;&#x43D;&#x430;&#x43B;</b>\n#&#x440;&#x435;&#x43A;&#x43B;&#x430;&#x43C;&#x430;\n&#x1F44E;|&#x1F44D;\n&#x1F926;&#x1F3FB;&#x200D;&#x2642;&#xFE0F;', 'http://mail.ru\nclc.to\nhttp://vk.com', 0, 0, '&lt;b&gt;Kill4me&lt;/b&gt; go', 0, '', 0, 0,'$time')");
		$setting_id = mysqli_insert_id($db);
		
		mysqli_query($db,"INSERT INTO `channels_origin` (`origin_name`,`publish_name`,`channel_publish`,`channel_origin`,`channel_origin_id`
																							,`channel_publish_id`,`setting_id`,`account_id`) 
											VALUES ('$origin_name','$publish_name','$channel_publish','$channel_origin','$channel_origin_id',
																					'$channel_publish_id','$setting_id','$self_account_id')");
		$id = mysqli_insert_id($db);
		
		$opt_p = '';
		$opt_p_upate = '';
		$opt_p_mv = "<option value='0'></option>";
		$rx1 = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`channel_id` FROM `join_exists` WHERE `type` = '2' AND `profile_tg` = '$pname'");
		while($list_p = mysqli_fetch_assoc($rx1))
		{ 
			if($list_p['channel_url'] == $channel_publish){ $sel = 'selected'; } else { $sel = ''; }
			$name_ch = $list_p['name'];
			if(mb_strlen($name_ch) > 50){ $name_ch = substr($name_ch,0,50); }
			$opt_p .= "<option value='".$list_p['id']."' $sel>".$name_ch."</option>";
			$opt_p_upate .= "<option value='".$list_p['channel_url']."'>".$name_ch."</option>"; 
			$opt_p_mv .= "<option value='".$list_p['channel_id']."'>".$name_ch."</option>"; 
		}
		
		$opt_p_upate .= "<option value='0'>Указать другой канал<option>";
		$opt_o = '';
		$opt_o_upate = '';
		$rx2 = mysqli_query($db,"SELECT `id`,`name`,`channel_url` FROM `join_exists` WHERE `type` = '1' AND `profile_tg` = '$pname'");
		while($list_o = mysqli_fetch_assoc($rx2))
		{
			if($list_o['channel_url'] == $channel_origin){ $sel = 'selected'; } else { $sel = ''; }
			$name_ch = $list_o['name'];
			if(mb_strlen($name_ch) > 50){ $name_ch = mb_substr($name_ch,0,50); }
			$opt_o .= "<option value='".$list_o['id']."' $sel>".$name_ch."</option>";
			$opt_o_upate .= "<option value='".$list_o['channel_url']."'>".$name_ch."</option>"; 
		}
		
		$opt_o_upate .= "<option value='0'>Указать другой канал<option>";
		$opt_p = "<select class='select2 change_channel_publish' style='width:100%;' object_id='$id'><option value='0'>-</option>$opt_p</select>";
		$opt_o = "<select class='select2 change_channel_origin' style='width:100%;' object_id='$id'><option value='0'>-</option>$opt_o</select>";
	
		echo json_encode(array('stat'=>1,'id'=>$id,'setting_id'=>$setting_id,'channel_name'=>$publish_name,'origin_name'=>$origin_name,
												'name_o'=>html_entity_decode($origin_name),'name_s'=>html_entity_decode($publish_name),
												'opt_p'=>$opt_p,'opt_o'=>$opt_o,'opt_p_upate'=>$opt_p_upate,'opt_o_upate'=>$opt_o_upate,
												'opt_p_mv'=>$opt_p_mv));
	}
	
	
	if($act == 'del_channel')
	{
		$setting_id = intval($_POST['setting_id']);
		
		$rx = mysqli_query($db,"SELECT `channel_origin_id` FROM `channels_origin` WHERE `id` = '$setting_id'");
		$num = mysqli_num_rows($rx);
		if($num > 0)
		{
			$data = mysqli_fetch_assoc($rx);
			mysqli_query($db,"DELETE FROM `update_messages` WHERE `channel_id_from` = '".$data['channel_origin_id']."'");
		}
		
		mysqli_query($db,"DELETE FROM `channels_origin` WHERE `id` = '$id'");
		mysqli_query($db,"DELETE FROM `channels_settings` WHERE `id` = '$setting_id'");

		$r = mysqli_query($db,"SELECT `setting_id`,`origin_name`,`publish_name` FROM `channels_origin`");
		$opt = '';
		while($list = mysqli_fetch_assoc($r))
		{
			$opt .= "<option value='".$list['setting_id']."'>Источник: ".$list['origin_name']. ' | Канал публикации: '.$list['publish_name']."</option>";
		}

		echo json_encode(array('stat'=>1,'opt'=>$opt));
	}
	
	
	if($act == 'set_status_channel')
	{
		$status = intval($_POST['status']);
		
		mysqli_query($db,"UPDATE `channels_origin` SET `status` = '$status' WHERE `id` = '$id'");
		echo json_encode(array('stat'=>1));
	}

	if($act == 'get_setting_channel')
	{
		$r = mysqli_query($db,"SELECT `id`,`status`, `spam_filter`, `replace_words`, `filter_links`, `filter_message`, 	`enable_text_message`, 
								`my_text_message`, `filter_inline`, `replace_link`, `enable_bot_vote`, `skip_text`, `time_post`,`timetable`, 
												`limit_post`,`limit_hours`,`limit_status`,`forward_message`,`ignore_post_type`,`sugn_channel`, 
									`word_send_post_func`,`word_send_post`,`word_send_post_type`,`replace_username`,`replace_username_stat`, 
										`update_message`,`delete_message`,`reply_post`,`length_message_limit`,`length_message`,`type_check_m`,
										`schedule`,`schedule_date`,`send_text_no_media`,`post_no_webpage`,`ignore_album`,`ignore_audio`,
										`duplicat_dis` FROM `channels_settings` WHERE `id` = '$id'");
		$data_x = mysqli_fetch_assoc($r);
		
		$time_post = explode("|",$data_x['time_post']);
		echo json_encode(array(
						'stat'=>1,
						'status'=>intval($data_x['status']),
						'spam_filter'=>unicode_char($data_x['spam_filter']),
						'replace_words'=>unicode_char($data_x['replace_words']),
						'filter_links'=>$data_x['filter_links'],
						'filter_message'=>intval($data_x['filter_message']),
						'enable_text_message'=>intval($data_x['enable_text_message']),
						'my_text_message'=>html_entity_decode($data_x['my_text_message']),
						'filter_inline'=>intval($data_x['filter_inline']),
						'replace_link'=>$data_x['replace_link'],
						'enable_bot_vote'=>intval($data_x['enable_bot_vote']),
						'skip_text'=>intval($data_x['skip_text']),
						'timetable'=>intval($data_x['timetable']),
						'time_post'=>$time_post,						
						'limit_status'=>intval($data_x['limit_status']),
						'limit_post'=>intval($data_x['limit_post']),
						'limit_hours'=>intval($data_x['limit_hours']),
						'ignore_post_type'=>intval($data_x['ignore_post_type']),
						'sugn_channel'=>unicode_char($data_x['sugn_channel']),
						'word_send_post_func'=>intval($data_x['word_send_post_func']),
						'word_send_post'=>unicode_char($data_x['word_send_post']),
						'word_send_post_type'=>intval($data_x['word_send_post_type']),
						'replace_username_stat'=>intval($data_x['replace_username_stat']),
						'replace_username'=>$data_x['replace_username'],
						'update_message'=>intval($data_x['update_message']),
						'delete_message'=>intval($data_x['delete_message']),
						'reply_post'=>intval($data_x['reply_post']),
						'length_message_limit'=>intval($data_x['length_message_limit']),
						'length_message'=>intval($data_x['length_message']),
						'type_check_m'=>intval($data_x['type_check_m']),
						'schedule'=>intval($data_x['schedule']),
						'schedule_date'=>$data_x['schedule_date'],
						'send_text_no_media'=>intval($data_x['send_text_no_media']),
						'post_no_webpage'=>intval($data_x['post_no_webpage']),
						'ignore_album'=>intval($data_x['ignore_album']),
						'ignore_audio'=>intval($data_x['ignore_audio']),
						'duplicat_dis'=>intval($data_x['duplicat_dis']),
						'forward_message'=>intval($data_x['forward_message'])));
	}
	
	if($act == 'set_publish_channel')
	{		
		$object_id = intval($_POST['object_id']);
		
		$rx = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`channel_id` FROM `join_exists` WHERE `id` = '$id'");
		$data_x = mysqli_fetch_assoc($rx);
		$channel_publish = $data_x['channel_url'];
		$channel_id = $data_x['channel_id'];
		$publish_name = $data_x['name'];
		
		mysqli_query($db,"UPDATE `channels_origin` SET `channel_publish` = '$channel_publish', `channel_publish_id` = '$channel_id',
																		`publish_name` = '$publish_name' WHERE `id` = '$object_id'");
		echo json_encode(array('stat'=>1));
	}
	
	if($act == 'set_origin_channel')
	{		
		$object_id = intval($_POST['object_id']);
		
		$rx = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`channel_id` FROM `join_exists` WHERE `id` = '$id'");
		$data_x = mysqli_fetch_assoc($rx);
		$channel_origin = $data_x['channel_url'];
		$channel_id = $data_x['channel_id'];
		$origin_name = $data_x['name'];

		mysqli_query($db,"UPDATE `channels_origin` SET `channel_origin` = '$channel_origin', `channel_origin_id` = '$channel_id',
																		`origin_name` = '$origin_name' WHERE `id` = '$object_id'");
		echo json_encode(array('stat'=>1));
	}
	
	if($act == 'set_current_account')
	{
		$_SESSION['self_account_id'] = $id;
		mysqli_query($db,"UPDATE `setting` SET `self_account_id` = '$id' WHERE `id` = '1'");
		echo json_encode(array('stat'=>1));
	}
	
	if($act == 'del_account')
	{
		$p = get_setting_account('profile_tg',$id);
		$profile = $_SERVER['DOCUMENT_ROOT'].root_dir.'/accounts/'.$p;
		if(file_exists($profile)){ unlink($profile);unlink($profile.'.lock');unlink($profile.'.safe.php');unlink($profile.'.safe.php.lock'); }
		
		$r = mysqli_query($db,"SELECT `id` FROM `accounts_setting` LIMIT 0,1");
		$data_x = mysqli_fetch_assoc($r);
		$id_account = intval($data_x['id']);
		mysqli_query($db,"UPDATE `setting` SET `self_account_id` = '$id_account' WHERE `id` = '1'");
		
		mysqli_query($db,"DELETE FROM `join_exists` WHERE `profile_tg` = '$p'");
		mysqli_query($db,"DELETE FROM `accounts_setting` WHERE `id` = '$id'");
		mysqli_query($db,"DELETE FROM `channels_origin` WHERE `account_id` = '$id'");
		echo json_encode(array('stat'=>1));
	}
	
	
	if($act == 'change_name_channel')
	{
		$name = strip_tags($_POST['name']);
		
		require_once 'vendor/autoload.php';

		$settings['app_info']['api_id'] = 16282;
		$settings['app_info']['api_hash'] = 'ebfaa22710b9372b4679b8e81d5bf264';
		$settings['logger']['logger'] = 0;
		
		$name_html = unicode_html($name);
		
		$self_account_id = $_SESSION['self_account_id'];
		$profile = get_setting_account('profile_tg',$self_account_id);
		$profile = $_SERVER['DOCUMENT_ROOT'].root_dir.'/accounts/p_'.$profile;
		try
		{
			$MadelineProto = new \danog\MadelineProto\API($profile,$settings);
			$MadelineProto->start();
			
			$MadelineProto->channels->editTitle(['channel' => '-100'.$id, 'title' => $name, ]);
			mysqli_query($db,"UPDATE `join_exists` SET `name` = '$name_html' WHERE `channel_id` = '$id'");
			mysqli_query($db,"UPDATE `channels_origin` SET `publish_name` = '$name_html' WHERE `channel_publish_id` = '$id'");
			
		}catch(\danog\MadelineProto\RPCErrorException $e) 
		{  	
			$error = $e->getMessage();
			echo json_encode(array('stat'=>0,'error'=>$error));
			exit;
		}
		
		echo json_encode(array('stat'=>1));
	}
	
	
	if($act == 'update_channels')
	{	
		$self_account_id = get_setting('self_account_id');
		$pname = get_setting_account('profile_tg',$self_account_id);
		if($self_account_id < 1){ echo json_encode(array('stat'=>0,'update'=>''));exit; }
		
		$r = mysqli_query($db,"SELECT `id`,`channel_origin`,`channel_publish`,`status`,`all_post`,`publish_name`,
											`origin_name`,`setting_id` FROM `channels_origin` WHERE `account_id` = '$self_account_id'");
		$all = mysqli_num_rows($r);
		if($all < 1){ echo json_encode(array('stat'=>0,'update'=>''));exit; }
		
		$tr = '';
		while($list = mysqli_fetch_assoc($r))
		{
			if($list['status'] == 0){ $btn1 = ''; $btn2 = "style='display:none;'"; } 
								 else{$btn1 = "style='display:none;'"; $btn2 = ''; }					
	  
			$tr .= "<tr class='item".$list['id']."'><td class='invisible_'><select class='select2 change_channel_publish' style='width:100%;' object_id='".$list['id']."'><option value='0'>-</option>";
											
				$rx1 = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`ready` FROM `join_exists` WHERE `type` = '2' 
																				AND `profile_tg` = '$pname'");
		
				while($list_p = mysqli_fetch_assoc($rx1))
				{
					if($list_p['channel_url'] == $list['channel_publish']){ $sel = 'selected'; } else { $sel = ''; }
					
					$name_ch = unicode_char($list_p['name']);
					if(mb_strlen($name_ch) > 50){ $name_ch = mb_substr($name_ch,0,50); }
					if($list_p['ready'] == 1){ $name_ch = "❗️ $name_ch - Отсуствует в аккаунте";$dis = 'disabled'; }else { $dis = ''; }
			
					$tr .= "<option value='".$list_p['id']."' $dis $sel>".$name_ch."</option>";
				}
				$tr .= "</select>";
				
				$tr .= "</td><td>
				<select class='select2 change_channel_origin' style='width:100%;' object_id='".$list['id']."'>
				<option value='0'>-</option>";
				
					$rx2 = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`ready` FROM `join_exists` WHERE `type` = '1' 
																					AND `profile_tg` = '$pname'");
					while($list_o = mysqli_fetch_assoc($rx2))
					{
						if($list_o['channel_url'] == $list['channel_origin']){ $sel = 'selected'; } else { $sel = ''; }
						
						$name_ch = unicode_char($list_o['name']); 
						if(mb_strlen($name_ch) > 50){ $name_ch = mb_substr($name_ch,0,50); }
						if($list_o['ready'] == 1){ $name_ch = "❗️ $name_ch - Отсуствует в аккаунте";$dis = 'disabled'; }else { $dis = ''; }
				
						$tr .= "<option value='".$list_o['id']."' $dis $sel>$name_ch</option>";
				}
				$tr .= "</select></td>";
				
				
				$tr .= "<td class='invisible_ center'>".$list['all_post']."</td>";
				$tr .= "<td class='center'>";
				$tr .= "<button class='btn btn-primary btn_posted stop stop".$list['id']."' $btn1 value='".$list['id']."'><i class='fa fa-stop' ></i> Стоп</button>
					<button class='btn btn-primary btn_posted start start".$list['id']."' $btn2 value='".$list['id']."'><i class='fa fa-play' ></i> Старт</button>
				
				</td>
				<td class='center'><i class='fa fa-trash del_channel'  value='".$list['id']."' setting_id='".$list['setting_id']."'></i></td></tr>";
		}

			echo json_encode(array('stat'=>1,'update'=>$tr));
	}
	
	
	$account_id = intval($_POST['account_id']);
	if($act == 'export_run')
	{	
		if($account_id < 1){ exit; }
	
		$r1 = mysqli_query($db,"SELECT * FROM `join_exists` WHERE `id_account` = '$account_id'");
		$arr_join = array();
		while($list = mysqli_fetch_assoc($r1))
		{
			$arr_join[] = $list;
		}		

		$r = mysqli_query($db,"SELECT * FROM `channels_origin` WHERE `account_id` = '$account_id'");
		$channels = array();
		$st = 0;
		while($list = mysqli_fetch_assoc($r))
		{
			$setting_id = $list['setting_id'];
			$rx = mysqli_query($db,"SELECT * FROM `channels_settings` WHERE `id` = '$setting_id'");
			
			$list_opt = mysqli_fetch_assoc($rx);
			$channels[$st]['settings'] = $list_opt;
			$channels[$st]['origin'] = $list;
			$st++;
		}
		
		if(empty($channels)){ echo json_encode(array('stat'=>2,));exit; }
		
		$status = get_setting_account('status',$account_id);
		$bot_token = get_setting_account('bot_token',$account_id);
		$bot_username = get_setting_account('bot_username',$account_id);
		$profile = get_setting_account('profile_tg',$account_id);
		$profile = str_replace('.tg','',$profile);
		
		$arr_glob['status'] = $status;
		$arr_glob['bot_token'] = $bot_token;
		$arr_glob['bot_username'] = $bot_username;
	
		$raw = json_encode(array('join'=>$arr_join,'channels'=>$channels,'glob'=>$arr_glob),true);
		$raw = base64_encode($raw);
		
		$file = $profile.'.dump';
		$dump = $_SERVER['DOCUMENT_ROOT'].download_dir.'/'.$file;
		$f = fopen($dump,'w');
		if(fwrite($f,$raw) == false)
		{
			fclose($f);
			echo json_encode(array('stat'=>0,));
			exit;
		}
		
		fclose($f);
		$u = $ssl.$_SERVER['SERVER_NAME'].root_dir.'/index.php?file='.$file;
		echo json_encode(array('stat'=>1,'u'=>$u));
	}
	
	
	if($act == 'upload_import_file')
	{
		$account_id = intval($_POST['account_id']);
		$_SESSION['self_account_id'] = $account_id;
		mysqli_query($db,"UPDATE `setting` SET `self_account_id` = '$account_id' WHERE `id` = '1'");
		
		if(empty($_FILES['file']['tmp_name'])){ echo json_encode(array('stat'=>0,));exit; }
		$tmp = $_FILES['file']['tmp_name'];
		
		$raw = file_get_contents($tmp);		
		$json = json_decode(base64_decode($raw),true);
		if((empty($json)) or ($account_id < 1)){ echo json_encode(array('stat'=>0,));exit; }
		unlink($tmp);
		
		$cols_join = array_keys($json['join'][0]);
		$cols_origin = array_keys($json['channels'][0]['origin']);
		$cols_opt = array_keys($json['channels'][0]['settings']);
		
		$profile = get_setting_account('profile_tg',$account_id);
		
		$status = $json['glob']['status'];
		$bot_token = $json['glob']['bot_token'];
		$bot_username = $json['glob']['bot_username'];
		$restart = $json['glob']['restart'];
		mysqli_query($db,"UPDATE `accounts_setting` SET `status` = '$status',`bot_token` = '$bot_token',
								 `bot_username` = '$bot_username',`restart` = '$restart' WHERE `id` = '$account_id'");
		kill_all_process($account_id);
		
		if((count($cols_join) < 1) && (count($cols_origin) < 1)){ echo json_encode(array('stat'=>0,));exit; }
		
		$s_cols = '';		
		$st = 0;
		foreach($json['join'] as $item)
		{
			$s_params = '';
			for($q=0;$q<count($cols_join);$q++)
			{
				$col = $cols_join[$q];
				
				switch($col)
				{
					case 'id':
						continue(2);
						break;
					case 'id_account':
						$value = $account_id;
						break;
					case 'ready':
						$value = 1;
						break;
					case 'profile_tg':
						$value = $profile;
						break;
					default:
						$value = $item[$col];
				}
				
				if($st == 0){ $s_cols .= "`$col`,"; }
				$s_params .= "'$value',";
			}
			
			$u = $item['channel_url'];
			$t = $item['type'];
			$rx = mysqli_query($db,"SELECT `id` FROM `join_exists` WHERE `channel_url` = '$u' AND `id_account` = '$account_id' 
																										AND `type` = '$t'");
			$is = mysqli_num_rows($rx);
			if($is > 0){ continue(1); }
			
			if($st == 0){ $s_cols = mb_substr($s_cols,0,mb_strlen($s_cols)-1); }
			$s_params = mb_substr($s_params,0,mb_strlen($s_params)-1);
			mysqli_query($db,"INSERT INTO `join_exists` ($s_cols) VALUES ($s_params)");
			$st++;
		}
		
		$s_cols = '';		
		$st = 0;
		foreach($json['channels'] as $item)
		{
			$s_params = '';
			for($q=0;$q<count($cols_opt);$q++)
			{
				$col = $cols_opt[$q];
				$value = $item['settings'][$col];
				
				if($col == 'id'){ continue; }
				
				if($st == 0){ $s_cols .= "`$col`,"; }
				$s_params .= "'$value',";
			}
			
			if($st == 0){ $s_cols = mb_substr($s_cols,0,mb_strlen($s_cols)-1); }
			$s_params = mb_substr($s_params,0,mb_strlen($s_params)-1);				
			$st++;
	
			mysqli_query($db,"INSERT INTO `channels_settings` ($s_cols) VALUES ($s_params)");
			$setting_id = mysqli_insert_id($db);
		
			$s_cols_o = '';		
			$st1 = 0;
			$s_params = '';
			for($q=0;$q<count($cols_origin);$q++)
			{
				$col = $cols_origin[$q];
				
				switch($col)
				{
					case 'id':
						continue(2);
					break;
					case 'account_id':
						$value = $account_id;
						break;
					case 'setting_id':
						$value = $setting_id;
						break;
					default:
						$value = $item['origin'][$col];
				}
				
				if($st1 == 0){ $s_cols_o .= "`$col`,"; }
				$s_params .= "'$value',";
			}
			
			$u1 = $item['origin']['channel_origin'];
			$u2 = $item['origin']['channel_publish'];
			$rx = mysqli_query($db,"SELECT `id` FROM `channels_origin` WHERE `channel_origin` = '$u1' AND `channel_publish` = '$u2' 
																								AND `account_id` = '$account_id'");
			$is = mysqli_num_rows($rx);
			if($is > 0){ mysqli_query($db,"DELETE FROM `channels_settings` WHERE `id` = '$setting_id'");continue(1); }
			
			if($st1 == 0){ $s_cols_o = mb_substr($s_cols_o,0,mb_strlen($s_cols_o)-1); }
			$s_params = mb_substr($s_params,0,mb_strlen($s_params)-1);
			
			mysqli_query($db,"INSERT INTO `channels_origin` ($s_cols_o) VALUES ($s_params)");
			$st1++;
		}
		mysqli_query($db,"DELETE FROM `join_exists` WHERE `channel_url` > 0 AND `id_account` = '$account_id'");
		
		echo json_encode(array('stat'=>1,));
	}
	
	
	if($act == 'set_join_exist')
	{
		mysqli_query($db,"UPDATE `join_exists` SET `ready` = '0' WHERE `id` = '$id'");
		
		$self_account_id = $_SESSION['self_account_id'];
		$r = mysqli_query($db,"SELECT `id` FROM `join_exists` WHERE `id_account` = '$self_account_id' AND `ready` = '1'");
		$all = mysqli_num_rows($r);
		
		echo json_encode(array('stat'=>1,'all'=>$all));
	}
	
	if($act == 'del_channel_join')
	{
		mysqli_query($db,"DELETE FROM `join_exists` WHERE `id` = '$id'");
		
		$self_account_id = $_SESSION['self_account_id'];
		$r = mysqli_query($db,"SELECT `id` FROM `join_exists` WHERE `id_account` = '$self_account_id' AND `ready` = '1'");
		$all = mysqli_num_rows($r);
		
		echo json_encode(array('stat'=>1,'all'=>$all));
	}
	
	if($act == 'join_channel')
	{
		$account_id = intval($_POST['account_id']);
		$error = '';
		
		$profile = get_setting_account('profile_tg',$account_id);
		$rx = mysqli_query($db,"SELECT `channel_url` FROM `join_exists` WHERE `id` = '$id'");
		$data = mysqli_fetch_assoc($rx);
		$channel_url = $data['channel_url'];
		
		require_once 'vendor/autoload.php';
		$settings['app_info']['api_id'] = 16282;
		$settings['app_info']['api_hash'] = 'ebfaa22710b9372b4679b8e81d5bf264';
		$settings['logger']['logger'] = 0;
		$settings['ipc']['slow'] = true;
		
		$profile = str_replace('+','',$profile);
		$profile = $_SERVER['DOCUMENT_ROOT'].root_dir.'/accounts/p_'.$profile;
		if(!file_exists($profile)){ echo json_encode(array('stat'=>0,'error'=>'Профиль отсуствует'));exit; }
	
		try
		{
			$MadelineProto = new \danog\MadelineProto\API($profile,$settings);
			$MadelineProto->start();
			try
			{
				$MadelineProto->channels->joinChannel(['channel' => $channel_url, ]);
				mysqli_query($db,"UPDATE `join_exists` SET `ready` = '0' WHERE `id` = '$id'");
			}catch(\danog\MadelineProto\RPCErrorException $e)
			{ 
				$error = $e->getMessage();
				if($error == 'USER_ALREADY_PARTICIPANT')
				{
					mysqli_query($db,"UPDATE `join_exists` SET `ready` = '0' WHERE `id` = '$id'");
					$r = mysqli_query($db,"SELECT `id` FROM `join_exists` WHERE `id_account` = '$account_id' AND `ready` = '1'");
					$all = mysqli_num_rows($r);
			
					echo json_encode(array('stat'=>0,'all'=>$all,'error'=>'Вы уже состоите в канале.'));
					unset($MadelineProto);
					exit;
				}else
				{
					echo json_encode(array('stat'=>0,'error'=>$error));
					unset($MadelineProto);
					exit;
				}
			} 
			
		}catch(\danog\MadelineProto\RPCErrorException $e)
		{ 
			$error = $e->getMessage();
		} 
		
		$r = mysqli_query($db,"SELECT `id` FROM `join_exists` WHERE `id_account` = '$account_id' AND `ready` = '1'");
		$all = mysqli_num_rows($r);
		
		echo json_encode(array('stat'=>1,'all'=>$all));
		unset($MadelineProto);
	}
	
	
	if($act == 'get_join_import_account')
	{
		$account_id = intval($_POST['account_id']);
		
		$r = mysqli_query($db,"SELECT `id`,`channel_url`,`name` FROM `join_exists` WHERE `id_account` = '$account_id' AND `ready` = '1'");
		$all_join = mysqli_num_rows($r);
		
		$list_tr = '';
		while($list = mysqli_fetch_array($r))
		{
			$u = "<a href='".$list['channel_url']."' target='_blank'>".$list['name']."</a>";
			$id = $list['id'];
			$list_tr .= "<tr class='join_wait$id'>
				<td>$u</td>
				<td>
					<button type='button' class='btn btn-danger btn-xs join_channel' value='$id'><i class='fa fa-plus-square'></i> Вступить</button>
					<button type='button' class='btn btn-success btn-xs join_exist' value='$id'> Уже там</button>
				</td>
				<td class='center'><i class='fa fa-trash del_channel_join' value='$id'></i></td>
			</tr>";
			
		}
		
		echo json_encode(array('stat'=>1,'list'=>$list_tr));
	}
	
	if($act == 'multi_save_setting')
	{
		$list_id = explode(':',$_POST['list_id']);
		$list_id = array_filter($list_id);
		$col_name = strip_tags($_POST['col_name']);
		$col_type = intval($_POST['col_type']);
		
		if($col_type == 1)
		{
			$col_value = strip_tags($_POST['col_value']);
			$col_value = filter_var($col_value, FILTER_VALIDATE_BOOLEAN);
			if($col_value == true){ $col_value = 1; } else { $col_value = 0; }
		}elseif($col_type == 2)
		{
			$col_value = unicode_html($_POST['col_value']);
		}else{ exit; }
		
		foreach($list_id as $opt_id)
		{
			$opt_id = intval($opt_id);
			mysqli_query($db,"UPDATE `channels_settings` SET `$col_name` = '$col_value' WHERE `id` = '$opt_id'");
		}
		
		echo json_encode(array('stat'=>1));
	}
	
?>