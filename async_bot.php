<?php
	use danog\MadelineProto\API;
	use danog\MadelineProto\EventHandler;
	use danog\MadelineProto\Logger;
	use danog\MadelineProto\Settings;
	
	class EventHandlerPoster extends EventHandler
	{
		public function __construct($MadelineProto)
		{
			parent::__construct($MadelineProto);
		}
		
		public function OnUpdateDeleteChannelMessages(array $update)
		{	
			global $db;
			$channel_id = $update['channel_id'];
			
			$rx = mysqli_query($db,"SELECT `setting_id`,`account_id` FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id'");
			$all = mysqli_num_rows($rx);
			if($all == 0){ return false; }
			
			while($list = mysqli_fetch_assoc($rx))
			{
				$account_id = $list['account_id'];
				
				$delete_message = get_setting_channel('delete_message',$list['setting_id']);
				if($delete_message == 0){ continue; }
				
				$enable_text_message = get_setting_channel('enable_text_message',$list['setting_id']);
				if($enable_text_message == 1){ continue; }
				
				if(!isset($update['messages'][0])){ return false; }
				
				foreach($update['messages'] as $message_id1)
				{
					$r = mysqli_query($db,"SELECT `id`,`message_id2`,`channel_id_to` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
																		AND `account_id` = '$account_id'AND `channel_id_from` = '$channel_id'");
					$num = mysqli_num_rows($r);
					if($num > 0)
					{
						$data_x = mysqli_fetch_assoc($r);
						$message_id2 = $data_x['message_id2']; 
						$channel_id_to = $data_x['channel_id_to'];
						yield $this->channels->deleteMessages(['channel'=>$channel_id_to,'id'=>[$message_id2,],]);
						mysqli_query($db,"DELETE FROM `update_messages` WHERE `id` = '".$data_x['id']."'");
					}
				}
			
			}
			
		}
		
		public function onUpdateEditChannelMessage(array $update)
		{	
			global $db;
			$channel_id = $update['message']['to_id']['channel_id'];
			
			$rx = mysqli_query($db,"SELECT `setting_id`,`account_id` FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id'");
			$all = mysqli_num_rows($rx);
			if($all == 0){ return false; }
			
			if(!isset($update['message']['to_id']['channel_id'])){ return false; }
			
			$message_id1 = $update['message']['id'];
			$message = $update['message']['message'];
			
			while($list = mysqli_fetch_assoc($rx))
			{
				$account_id = $list['account_id'];

				$update_message = get_setting_channel('update_message',$list['setting_id']);
				if($update_message == 0){ continue; }
				
				$enable_text_message = get_setting_channel('enable_text_message',$list['setting_id']);
				if($enable_text_message == 1){ continue; }
				
				$r = mysqli_query($db,"SELECT `message_id2`,`channel_id_to` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
																AND `channel_id_from` = '$channel_id' AND `account_id` = '$account_id'");
				$num = mysqli_num_rows($r);
				if($num > 0)
				{
					$data_x = mysqli_fetch_assoc($r);
					$message_id2 = $data_x['message_id2']; 
					$channel_id_to = $data_x['channel_id_to'];
					
					if(isset($update['message']['entities'])){ $entities = $update['message']['entities']; } else { $entities = array(); }
					if(isset($update['message']['document']['thumbs'])){ $thumbs = $update['message']['document']['thumbs']; } 
																									else { $thumbs = array(); }
				
					try{
						yield $this->messages->editMessage(['peer'=>$channel_id_to,'id'=>$message_id2,'message'=>$message,'entities'=>$entities,]);
					}catch (\danog\MadelineProto\RPCErrorException $e) 
					{ $error = $e->getMessage(); }
				}
			
			}
			
		}

		public function onUpdateNewChannelMessage(array $update)
		{	
			if(($update['message']['out']) && (isset($update['message']['message'])) && (!stristr($update['message']['message'],'update:'))){ return false; }
			
			global $db;
			global $path;
			global $grouped_old;
			global $num_grouped;
			global $singleMedia;
			global $api_bot_url;
			global $domain;
			global $message;
			global $message_list;
			global $id_account;
			global $send_ping;
			global $album_info;

			$file_id = 0;
			$file = '';
			$image_size = [];
			$screenshot = null;
			$status = get_setting_account('status',$id_account);
			if($status == 1){ exit; }
			
			
			$channel_id_update = get_setting_account('channel_id_update',$id_account);
			if(empty($channel_id_update))
			{
				$updates = yield $this->channels->createChannel([ 'title' => 'UPDATE', 'about' => 'UPDATE POST',]);
				if(isset($updates['chats'][0]['id']))
				{
					$channel_id_update = '-100'.$updates['chats'][0]['id'];
					mysqli_query($db,"UPDATE `accounts_setting` SET `channel_id_update` = '$channel_id_update' WHERE `id` = '$id_account'");
				}
			}

			$message = '';
			if((isset($update['message']['message'])) && (!empty($update['message']['message']))){ $message = $update['message']['message']; } 
	
			if(isset($update['message']['entities'])){ $entities = $update['message']['entities']; } else { $entities = array(); }
			if(isset($update['message']['document']['thumbs'])){ $thumbs = $update['message']['document']['thumbs']; } else { $thumbs = array(); }
			if(isset($update['message']['grouped_id'])){ $grouped_id = $update['message']['grouped_id']; } else { $grouped_id = 0; }
			$channel_id = $update['message']['to_id']['channel_id'];
			
			if((isset($update['message']['media']['document']['attributes'][1]['_'])) && ($update['message']['media']['document']['attributes'][1]['_'] == 'documentAttributeSticker')){ return false; }

			if($grouped_id > 0)
			{	
				if(($grouped_old !== $grouped_id) && ($grouped_old > 0) && ($grouped_id > 0)) # return false;
				{
					$singleMedia = [];
					$num_grouped = 0;
					$grouped_id = 0;
					$grouped_old = 0;
					$message_list = array();
					$recv_p = array();
				}
		
				$grouped_old = $grouped_id;
	
				$rx1 = mysqli_query($db,"SELECT `id`,`setting_id` FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id' 
																								AND `account_id` = '$id_account'");
				$num = mysqli_num_rows($rx1);
				if($num == 0){ return false; }
				else
				{
					$st = 0;
					while($list_x = mysqli_fetch_assoc($rx1))
					{
						$ignore_album = get_setting_channel('ignore_album',$list_x['setting_id']);
						if($ignore_album == 1){ $st++; }
					}
				
					if($st == $num)
					{
						$singleMedia = [];
						$num_grouped = 0;
						$grouped_id = 0;
						$grouped_old = 0;
						$message_list = array();
						$recv_p = array();
						
						return false;
					}
				}

				$message_list[] = $update['message']['id'];
				if((!empty($message)) && ($album_info == 0))
				{
					$album_info = 1;
					$singleMedia[0]['message'] = $message;
				}else { $entities = array(); }
				
				if(isset($update['message']['media']['document']))
				{
					$inputDocument = ['_' => 'inputDocument', 'id' => $update['message']['media']['document']['id'], 
														'access_hash' => $update['message']['media']['document']['access_hash'], 
														'file_reference' => $update['message']['media']['document']['file_reference'],];
					$inputSingleMedia = ['_' => 'inputMediaDocument', 'id' => $inputDocument,];
				}elseif(isset($update['message']['media']['photo']))
				{
					$inputPhoto = ['_' => 'inputPhoto', 'id' => $update['message']['media']['photo']['id'], 
									'access_hash' => $update['message']['media']['photo']['access_hash'], 
									'file_reference' => $update['message']['media']['photo']['file_reference'],
								];
					$inputSingleMedia = ['_' => 'inputMediaPhoto', 'id' => $inputPhoto,];
				}
			
				$singleMedia[$num_grouped]['_'] = 'inputSingleMedia';
				$singleMedia[$num_grouped]['media'] = $inputSingleMedia;
				$singleMedia[$num_grouped]['entities'] = $entities;
	
				$num_grouped++;
				mysqli_query($db,"UPDATE `channels_origin` SET `album_get` = '1' WHERE `channel_origin_id` = '$channel_id' AND `status` = '0' 
																										AND `account_id` = '$id_account'");
				if($send_ping == 0)
				{
					$send_ping = 1;
					if(empty($channel_id_update)){ return false; }
					
					$date = strtotime('+8 seconds',time());
					$info = 'update:'.$channel_id_update.':'.$channel_id;
					$recv = yield $this->messages->sendMessage(['peer' => $channel_id_update, 'message' => $info,'schedule_date'=>$date,
																'silent'=>false,]);
				}
				
				return false;
			}
	
			if(($num_grouped > 0) or (stristr($message,'update:')))
			{
				$send_ping = 0;
				
			    if(mb_strpos($message,':')){ list($message,$channel_id_update,$channel_id) = explode(':',$message); }			
				$rx1 = mysqli_query($db,"SELECT `id`,`channel_publish_id`,`channel_publish`,`all_post`,`status`,`setting_id` 
											FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id' AND `account_id` = '$id_account'");
				$num = mysqli_num_rows($rx1);
				if($num == 0){ return false; }
				
				if(!isset($singleMedia[0]['message']))
				{ 
					$singleMedia = [];
					$num_grouped = 0;
					$grouped_id = 0;
					$grouped_old = 0;
					$message_list = array();
					$album_info = 0;
					return false; 
				}

				$message = $singleMedia[0]['message'];
				$entities = $singleMedia[0]['entities'];

				$full_dialogs = yield $this->get_full_dialogs();
				
				while($tasks = mysqli_fetch_assoc($rx1))
				{
					$setting_id = $tasks['setting_id'];
					if($tasks['status'] == 1){ continue; }
					
					$ignore_album = get_setting_channel('ignore_album',$setting_id);
					if($ignore_album == 1){ continue; }
					
					$reply_to = get_setting_channel('reply_post',$setting_id);
					if($reply_to == 1)
					{
						if(isset($update['message']['reply_to']['reply_to_msg_id'])){ continue; }
					}
					
					$length_message_limit = get_setting_channel('length_message_limit',$setting_id);
					if($length_message_limit == 1)
					{
						$length = get_setting_channel('length_message',$setting_id);
						$type_check_m = get_setting_channel('type_check_m',$setting_id);
						if(!isset($type_check_m)){ $type_check_m = 0; }
						$type_check_m = intval($type_check_m);
						if(($length > 0) && ($type_check_m > 0))
						{
							if(($type_check_m == 1) && (mb_strlen($message) < $length)){ return false; }
							elseif(($type_check_m == 2) && (mb_strlen($message) > $length)){ return false; }
						}
					}
					
					$channel_id_to = '-100'.$tasks['channel_publish_id'];
					if(!isset($full_dialogs[$channel_id_to])){ $status_send = 1; }else { $status_send = 0; }
					if($status_send == 1){ continue; }
					$all_post = $tasks['all_post'];
					$all_post++;
				
						# FILTER CODE
						$send_multi = true;		
						$r_user_data = mysqli_query($db,"SELECT `spam_filter`,`replace_words`,`filter_message`,`filter_inline`,
														`enable_text_message`,`my_text_message`,`replace_link`,`skip_text`,`filter_links`,
														 `timetable`,`time_post`,`limit_status`,`limit_post`,`limit_hours`,`limit_time`,
														 `limit_self`,`forward_message`,`ignore_post_type`,`sugn_channel`,`word_send_post_func`,
														  `word_send_post`,`word_send_post_type`,`replace_username`,`replace_username_stat`,
														  `schedule`,`schedule_date`,`send_text_no_media`,`ignore_audio`,`duplicat_dis` 
																						FROM `channels_settings` WHERE `id` = '$setting_id'");
						$is_data = mysqli_num_rows($r_user_data);
						if($is_data == 0){ continue; } # return false;
				
						$user_data = mysqli_fetch_assoc($r_user_data);
						if($user_data['send_text_no_media'] == 1){ continue; } # return false
						
						$schedule = intval($user_data['schedule']);
						$schedule_date = $user_data['schedule_date'];
						if(strstr($user_data['schedule_date'],'-'))
						{
							$schedule_arr = explode('-',$user_data['schedule_date']);
							$schedule_date = $schedule_arr[array_rand($schedule_arr)];
						}
						 
						if(empty($schedule_date)){ $schedule_date = 0; }
						if(($schedule_date > 0) && ($schedule == 1))
						{
							$schedule_date = strtotime("+$schedule_date hour",time());
						}else { $schedule_date = 0; }
							
						$forward_message = intval($user_data['forward_message']);
						
						if(($user_data['timetable'] == 1) && (!empty($user_data['time_post'])))
						{
							if(!mb_strstr($user_data['time_post'],date('H'))){ continue; }
						}
						
						
						$spam_filter = unicode_char($user_data['spam_filter']);
						$replace_words = htmlspecialchars_decode($user_data['replace_words']);
						$filter_message = intval($user_data['filter_message']);
						$filter_inline = $user_data['filter_inline'];
						$enable_text_message = $user_data['enable_text_message'];
						$my_text_message = $user_data['my_text_message'];
						$replace_link = $user_data['replace_link'];
						$skip_text = $user_data['skip_text'];
						$filter_links = $user_data['filter_links'];
						$ignore_post_type = $user_data['ignore_post_type'];
						$sugn_channel = unicode_char($user_data['sugn_channel']);
						$word_send_post = unicode_char($user_data['word_send_post']);
						$word_send_post_func = $user_data['word_send_post_func'];
						$word_send_post_type = $user_data['word_send_post_type'];
						$replace_username = $user_data['replace_username'];
						$replace_username_stat = $user_data['replace_username_stat'];
						$ignore_audio = $user_data['ignore_audio'];
						$duplicat_dis = $user_data['duplicat_dis'];
				
						if($ignore_audio == 1)
						{
							if((isset($update['message']['media']['document']['mime_type'])) && (stristr($update['message']['media']['document']['mime_type'],'audio/'))){ continue; }
						}
												
						if(($filter_message > 0) && (strlen($message) > 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
						{
							preg_match_all('/(http:\/\/|https:\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', $message, $links);
							
							switch($filter_message)
							{
								case 1:
									foreach($links[0] as $u)
									{
										$message = str_replace($u,'',$message);
									}
									
									if(count($entities) > 0)
									{
										foreach($entities as $num=>$val)
										{
											if($val['_'] == 'messageEntityTextUrl'){ unset($entities[$num]); }
										}
										
										$entities = array_values(array_filter($entities));
									}
								break;
								case 2:
									foreach($links[0] as $u)
									{
										if((mb_strstr($u,'t.me')) or (mb_strstr($u,'telegram.me'))){ $message = str_replace($u,'',$message); }
									}
									
									preg_match_all('(@\w*)', $message, $list_s); 
									if(count($list_s[0]) > 0)
									{
										foreach($list_s[0] as $n=>$val)
										{
											$message = str_replace($val,'',$message);
										}
									}
									
									if(count($entities) > 0)
									{
										foreach($entities as $num=>$val)	
										{
											if($val['_'] == 'messageEntityTextUrl')
											{ 
												if((mb_strstr($val['url'],'t.me')) or (mb_strstr($val['url'],'telegram.me'))){ unset($entities[$num]); }
											}
										}
										
										$entities = array_values(array_filter($entities));
									}
								break;
								case 3:
									if((mb_strstr($message,'t.me')) or (mb_strstr($message,'telegram.me')) or (mb_strstr($message,'/joinchat')))
									{
										$send_multi = false; 
										continue(1);
									}
									if(count($entities) > 0)
									{
										foreach($entities as $num=>$val)	
										{
											if($val['_'] == 'messageEntityTextUrl')
											{ 
												if((mb_strstr($val['url'],'t.me')) or (mb_strstr($val['url'],'telegram.me'))){ 
													$send_multi = false;
													continue(2);
												}
											}
										}
									}
								break;
								case 4:
									if(count($links[0]) > 0){ $send_multi = false;continue(1); }
									if(count($entities) > 0)
									{
										foreach($entities as $num=>$val)	
										{
											if($val['_'] == 'messageEntityTextUrl')
											{ 
												if(isset($val['url'])){ $send_multi = false;continue(2); }
											}
										}
									}
								break;
								case 5:
									foreach($links[0] as $u)
									{
										$message = str_replace($u,$replace_link,$message);
									}
									
									if((count($entities) > 0) && (!empty($replace_link)))
									{	
										foreach($entities as $num=>$val)	
										{
											if($val['_'] == 'messageEntityTextUrl')
											{ 
												$entities[$num]['url'] = $replace_link;
											}
										}
									}
							}	
						}
					
						if(($filter_inline == 1) && (isset($update['message']['reply_markup'])))
						{
							if(($word_send_post_type == 0) && ($word_send_post_func == 1) or (($word_send_post_func == 0)))
							{
								$json = json_encode($update['message']['reply_markup'],true);
								$json = json_decode($json,true);
								foreach($json['rows'] as $inline)
								{
									foreach($inline['buttons'] as $inline_row)
									{
										if(isset($inline_row['url'])){ $send_multi = false;continue(2); }
									}
								}
							}
						}
						
						
						if((strlen($spam_filter) > 0) && (strlen($message) > 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
						{
							$words = explode("\n",$spam_filter);
							foreach($words as $val)
							{
								if(empty($val)){ continue; }
								if(mb_stristr($message,$val)){ $send_multi = false;continue(2); }								
								
								if(isset($update['message']['entities']))
								{
									foreach($update['message']['entities'] as $val_inline)
									{
										if($val_inline['_'] === 'messageEntityTextUrl')
										{ 
											if(mb_stristr($val_inline['url'],$val)){$send_multi = false;continue(2); }
										}
									}
								}
							}
						}
						
						if((strlen($word_send_post) > 0) && (strlen($message) > 0) && ($enable_text_message == 0) && ($word_send_post_func == 1))
						{
							$word_send_post_arr = explode("\n",$word_send_post);
							$find_word = false;
							foreach($word_send_post_arr as $val)
							{
								if(empty($val)){ continue; }
								if(mb_stripos($message,$val)){ $find_word = true;break; }
							}
							if($find_word == false){ return false; }
						}	
						
						if((strlen($replace_words) > 0) && (strlen($message) > 0) && ($enable_text_message == 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
						{
							$replace_words = unicode_char($replace_words);
							$words_repl = explode("\n",$replace_words);
							foreach($words_repl as $val)
							{
								if(empty($val)){ continue; }						
								if(!mb_strstr($val,'*'))
								{
									if(mb_strstr($val,'|'))
									{ 								
										list($val,$word) = explode('|',$val);								
									} else { $word = ''; }
									
									if(mb_stristr($message,$val))
									{ 	
										$message = mb_eregi_replace($val,$word,$message);								
									}
								}else
								{
									$message = message_replace($val,$message);
								}
							}
							
							$message = str_replace('[LINE]',"\n",$message);
						}
						
						if((strlen($filter_links) > 0) && (strlen($message) > 5) && ($enable_text_message == 0) && ($filter_message !== 1) && ($filter_message !== 4) && ($filter_message !== 5) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
						{	
							preg_match_all('/(http:\/\/|https:\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', $message, $links);
							$links = array_filter($links);
							
							if(isset($links[0]))
							{
								$filter_links = strtolower($filter_links);
								
								for($q=0;$q<count($links[0]);$q++)
								{
									$domain_name = str_replace('.','',$links[3][$q]);
									$domain = $domain_name.'.'.$links[4][$q];
									
									if((mb_stristr($filter_links,$domain)) && (!empty($domain)))
									{ 
										$message = str_replace($links[0][$q],'',$message); 
									}
									
								}
								
								#$message = preg_replace("/\r\n\r\n|\r\r|\n\n/", "\n", $message);
							}
						}	
						if(($enable_text_message == 1) && (strlen($my_text_message) > 0) && (strlen($message) > 0) && ($word_send_post_func == 0))
						{
							$my_text = unicode_char($my_text_message);
							$my_text = htmlspecialchars_decode($my_text);
							if(strstr($my_text,'|'))
							{ 
								$my_text = explode('|',$my_text); 
								$message = $my_text[array_rand($my_text)];
							}else { $message = $my_text; }
							$entities = array();
						}
			
						if((!empty($message)) && ($duplicat_dis == 0))
						{
							$hash = md5($message);
							$rx = mysqli_query($db,"SELECT `hash` FROM `duplicatus_post` WHERE `hash` = '$hash' AND `setting_id` = '$setting_id'");
							$ex = mysqli_num_rows($rx);
							if(($ex > 0) && ($enable_text_message == 0)){ continue; }
							mysqli_query($db,"INSERT INTO `duplicatus_post` (`hash`,`setting_id`) VALUES ('$hash','$setting_id')");
						}
						
						
						if(($replace_username_stat > 0) && ($enable_text_message == 0) && (strlen($message) > 0))
						{
							preg_match_all('(@\w*)', $message, $list_s); 
							if(count($list_s[0]) > 0)
							{
								foreach($list_s[0] as $n=>$val)
								{
									$message = str_replace($val,$replace_username,$message);
								}
							}
						}
					
						if(($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
						{	
							switch($ignore_post_type)
							{
								case 1:
									if((empty($message)) && (isset($update['message']['media']['_']))){ return false; }
									break;
								case 2:
									if(!empty($message)){ return false; }
									break;	
								case 3:	
									if((!empty($message)) && (!isset($update['message']['media']['_']))){ return false; }
									break;
								case 4:
									if((!empty($message)) && (isset($update['message']['media']['_']))){ return false; }
									break;
								case 5:
									if(empty($message)){ return false; }
									break;
								case 6:
									if((!empty($message)) && (isset($update['message']['media']['_']))){ return false; }
									break;
								case 7:
									if((!isset($update['message']['media']['_'])) or
										($update['message']['media']['_'] !== 'messageMediaDocument') &&
										(!isset($update['message']['media']['document']['_'])) &&
										($update['message']['media']['_'] !== 'messageMediaWebPage')){ return false; }
									break;
								case 8:
									if((!isset($update['message']['media']['_'])) or
										($update['message']['media']['_'] !== 'messageMediaPhoto') &&
										(!isset($update['message']['media']['webpage']['photo']['_']))){ return false; }
									break;
								case 9:
									if((isset($update['message']['media']['document']['mime_type'])) &&
										(stristr($update['message']['media']['document']['mime_type'],'video/'))){ return false; }
									break;
								case 10:
									if(isset($update['message']['media'])){ $message = '';$singleMedia[0]['message'] = ''; }
							}
						}
						# END FILTER CODE
						
						if($user_data['limit_status'] == 1)
						{
							$limit_self = $user_data['limit_self'];
							$hours = time() - $user_data['limit_time'];
							$hours = round($hours / 3600);
							
							if($hours >= $user_data['limit_hours'])
							{ 
								mysqli_query($db,"UPDATE `channels_settings` SET `limit_time` = '".time()."',`limit_self` = '0' 
																									WHERE `id` = '$setting_id'");
								$hours = 0;
								$limit_self = 0;																	
							}	
							
							if(($hours > $user_data['limit_hours']) or ($limit_self >= $user_data['limit_post'])){ return false; }
							
							if($limit_self <= $user_data['limit_post'])
							{
								$limit_self++;
								mysqli_query($db,"UPDATE `channels_settings` SET `limit_self` = '$limit_self' WHERE `id` = '$setting_id'");
							}
						}
						
						if(mb_strlen($sugn_channel) > 0)
						{
							$sugn_val = $sugn_channel;
							$sugn_text = unicode_char($sugn_val);
							$sugn_text = htmlspecialchars_decode($sugn_text);
							$sugn_x = '';
							
							if(strstr($sugn_text,'|'))
							{ 
								$sugn_text_arr = explode("\n",$sugn_text);
								$sugn_text = explode("|",$sugn_text);
								$sugn_channel = $sugn_text[array_rand($sugn_text)];
								
								foreach($sugn_text_arr as $v)
								{
									if(empty($v)){ $sugn_x .= "\n"; }
									else{ $sugn_x = str_replace($v,$sugn_channel,$sugn_val); }
								} 
							}else { $sugn_x = $sugn_text; }
							
							$message .= $sugn_x;
						}
						$message = strip_tags($message);
						$singleMedia[0]['message'] = $message;
						$singleMedia[0]['entities'] = $entities;
					
					$send_ping = 0;
					$album_info = 0;
					if($send_multi == true)
					{
						if($forward_message == 1){						
							yield $this->messages->forwardMessages(['grouped'=>true,'from_peer'=> $update,'id' =>$message_list,
																								'to_peer' => $channel_id_to,]);
						}else
						{
							try{
								$recv = yield $this->messages->sendMultiMedia([
									'peer' => $channel_id_to,
									'multi_media' => $singleMedia,
									'schedule_date'=>$schedule_date,
								]);			

								if($recv['updates'][0]['_'] === 'updateMessageID')
								{
									$message_id1 = $update['message']['id'];
									$message_id2 = $recv['updates'][0]['id'];
									
									$r = mysqli_query($db,"SELECT `id` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
																AND `account_id` = '$id_account' AND `channel_id_from` = '$channel_id'");
									$num = mysqli_num_rows($r);
									if($num == 0)
									{
										mysqli_query($db,"INSERT INTO `update_messages` (`message_id1`,`message_id2`,`channel_id_from`,
																										`channel_id_to`,`account_id`) 
													VALUES ('$message_id1','$message_id2','$channel_id','$channel_id_to','$id_account')");
									}
								}
							}catch (\danog\MadelineProto\RPCErrorException $e) 
							{ $error = $e->getMessage();continue; }
						}
						mysqli_query($db,"UPDATE `base_channels` SET `all_post` = '$all_post' WHERE `channel_id` = '$channel_id'");
					}
				}
				
				$singleMedia = [];
				$num_grouped = 0;
				$grouped_id = 0;
				$grouped_old = 0;
				$message_list = array();
				$album_info = 0;

				if(isset($update['message']['message'])){ $message = $update['message']['message']; } 
				if($message == 'update'){ return false; }
			}
				
				$rx_opt = mysqli_query($db,"SELECT `setting_id` FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id' 
																							AND `account_id` = '$id_account'");
				$ex_opt = mysqli_num_rows($rx_opt);
				if($ex_opt = 0){ return false; }
				$message_old = $message;
				while($data_opt = mysqli_fetch_assoc($rx_opt))
				{	
				
				$message = $message_old;
				if(empty($data_opt['setting_id'])){ continue; }
				$setting_id = $data_opt['setting_id'];
			
				$r_user_data = mysqli_query($db,"SELECT `spam_filter`,`replace_words`,`filter_message`,`filter_inline`,
												`enable_text_message`,`my_text_message`,`replace_link`,`skip_text`,`filter_links`,
									`limit_post`,`limit_hours`,`limit_time`,`limit_self`,`limit_status`,`ignore_post_type`,`sugn_channel`, 
									`word_send_post_func`,`word_send_post`,`word_send_post_type`,`replace_username`,`replace_username_stat`, 
									 `ignore_audio` FROM `channels_settings` WHERE `id` = '$setting_id'");
				$is_data = mysqli_num_rows($r_user_data);
				if($is_data == 0){ continue; }
		
				$user_data = mysqli_fetch_assoc($r_user_data);
				
				$spam_filter = unicode_char($user_data['spam_filter']);
				$replace_words = htmlspecialchars_decode($user_data['replace_words']);
				$filter_message = intval($user_data['filter_message']);
				$filter_inline = $user_data['filter_inline'];
				$enable_text_message = $user_data['enable_text_message'];
				$my_text_message = $user_data['my_text_message'];
				$replace_link = $user_data['replace_link'];
				$skip_text = $user_data['skip_text'];
				$filter_links = $user_data['filter_links'];
				$ignore_post_type = $user_data['ignore_post_type'];
				$sugn_channel = unicode_char($user_data['sugn_channel']);
				$word_send_post = unicode_char($user_data['word_send_post']);
				$word_send_post_func = $user_data['word_send_post_func'];
				$word_send_post_type = $user_data['word_send_post_type'];
				$replace_username = $user_data['replace_username'];
				$replace_username_stat = $user_data['replace_username_stat'];
				$ignore_audio = $user_data['ignore_audio'];
				
				if($ignore_audio == 1)
				{
					if((isset($update['message']['media']['document']['mime_type'])) && (stristr($update['message']['media']['document']['mime_type'],'audio/'))){ continue; }
				}
		
				if(($filter_message > 0) && (strlen($message) > 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
				{
					preg_match_all('/(http:\/\/|https:\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', $message, $links);

					switch($filter_message)
					{
						case 1:
							foreach($links[0] as $u)
							{
								$message = str_replace($u,'',$message);
							}
								#$message = preg_replace("/\r\n\r\n|\r\r|\n\n/", "\n", $message);
							
							if(count($entities) > 0)
							{
								foreach($entities as $num=>$val)
								{
									if($val['_'] == 'messageEntityTextUrl'){ unset($entities[$num]); }
								}
								
								$entities = array_values(array_filter($entities));
							}
						break;
						case 2:
							foreach($links[0] as $u)
							{
								if((mb_strstr($u,'t.me')) or (mb_strstr($u,'telegram.me'))){ $message = str_replace($u,'',$message); }
							}
							
							preg_match_all('(@\w*)', $message, $list_s); 
							if(count($list_s[0]) > 0)
							{
								foreach($list_s[0] as $n=>$val)
								{
									$message = str_replace($val,'',$message);
								}
							}
							
							if(count($entities) > 0)
							{
								foreach($entities as $num=>$val)	
								{
									if($val['_'] == 'messageEntityTextUrl')
									{ 
										if((mb_strstr($val['url'],'t.me')) or (mb_strstr($val['url'],'telegram.me'))){ unset($entities[$num]); }
									}
								}
								
								$entities = array_values(array_filter($entities));
							}
						break;
						case 3:
							if((mb_strstr($message,'t.me')) or (mb_strstr($message,'telegram.me')) or (mb_strstr($message,'/joinchat'))){ return false; }
							if(count($entities) > 0)
							{
								foreach($entities as $num=>$val)	
								{
									if($val['_'] == 'messageEntityTextUrl')
									{ 
										if((mb_strstr($val['url'],'t.me')) or (mb_strstr($val['url'],'telegram.me'))){ return false; }
									}
								}
							}
						break;
						case 4:
							if(count($links[0]) > 0){ return false; }
							if(count($entities) > 0)
							{
								foreach($entities as $num=>$val)	
								{
									if($val['_'] == 'messageEntityTextUrl')
									{ 
										if(isset($val['url'])){ return false; }
									}
								}
							}
						break;
						case 5:
							foreach($links[0] as $u)
							{
								$message = str_replace($u,$replace_link,$message);
							}
							
							if((count($entities) > 0) && (!empty($replace_link)))
							{
								foreach($entities as $num=>$val)	
								{
									if($val['_'] == 'messageEntityTextUrl')
									{ 
										$entities[$num]['url'] = $replace_link;
									}
								}
							}
					}	
				}
				
				if(($filter_inline == 1) && (isset($update['message']['reply_markup'])))
				{
					if(($word_send_post_type == 0) && ($word_send_post_func == 1) or (($word_send_post_func == 0)))
					{
						$json = json_encode($update['message']['reply_markup'],true);
						$json = json_decode($json,true);
						foreach($json['rows'] as $inline)
						{
							foreach($inline['buttons'] as $inline_row)
							{
								if(isset($inline_row['url'])){ return false; }
							}
						}
					}
				}
				
				if((strlen($spam_filter) > 0) && (strlen($message) > 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
				{
					$words = explode("\n",$spam_filter);
					foreach($words as $val)
					{	
						if(empty($val)){ continue; }
						if(mb_stristr($message,$val)){ return false; }
						
						if(isset($update['message']['entities']))
						{
							foreach($update['message']['entities'] as $val_inline)
							{
								if($val_inline['_'] === 'messageEntityTextUrl')
								{ 
									if(mb_stristr($val_inline['url'],$val)){ return false; }
								}
							}
						}
					}
				}
				
				if((strlen($replace_words) > 0) && (strlen($message) > 0) && ($enable_text_message == 0) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
				{
					$replace_words = unicode_char($replace_words);
					$words_repl = explode("\n",$replace_words);
					foreach($words_repl as $val)
					{
						if(empty($val)){ continue; }						
						if(!mb_strstr($val,'*'))
						{
							if(mb_strstr($val,'|'))
							{ 								
								list($val,$word) = explode('|',$val);								
							} else { $word = ''; }
							
							if(mb_stristr($message,$val))
							{ 	
								$message = mb_eregi_replace($val,$word,$message);								
							}
						}else
						{
							$message = message_replace($val,$message);
						}
					}
					
					$message = str_replace('[LINE]',"\n",$message);
				}	
				
				if((strlen($word_send_post) > 0) && (strlen($message) > 0) && ($enable_text_message == 0) && ($word_send_post_func == 1))
				{
					$word_send_post_arr = explode("\n",$word_send_post);
					$find_word = false;
					foreach($word_send_post_arr as $val)
					{
						if(empty($val)){ continue; }
						if(mb_stripos($message,$val)){ $find_word = true;break; }
					}
					if($find_word == false){ return false; }
				}	
				
				if((strlen($filter_links) > 0) && (strlen($message) > 5) && ($enable_text_message == 0) && ($filter_message !== 1) && ($filter_message !== 4) && ($filter_message !== 5) && ($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
				{	
					preg_match_all('/(http:\/\/|https:\/\/)?(www)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w\.-\?\%\&]*)*\/?/i', $message, $links);
					$links = array_filter($links);
					
					if(isset($links[0]))
					{
						$filter_links = strtolower($filter_links);
						
						for($q=0;$q<count($links[0]);$q++)
						{
							$domain_name = str_replace('.','',$links[3][$q]);
							$domain = $domain_name.'.'.$links[4][$q];
							
							if((mb_stristr($filter_links,$domain)) && (!empty($domain)))
							{ 
								$message = str_replace($links[0][$q],'',$message); 
							}
							
						}
						
						#$message = preg_replace("/\r\n\r\n|\r\r|\n\n/", "\n", $message);
					}
				
				}

				if(($skip_text == 1) && (!isset($update['message']['media']['_'])))
				{
					if(($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0)){ return false; }
				}			
				
				if(($enable_text_message == 1) && (strlen($my_text_message) > 0) && (strlen($message) > 0) && ($word_send_post_func == 0))
				{
					$my_text = unicode_char($my_text_message);
					$my_text = htmlspecialchars_decode($my_text);
					if(strstr($my_text,'|'))
					{ 
						$my_text = explode('|',$my_text); 
						$message = $my_text[array_rand($my_text)];
					}else { $message = $my_text; }
					$entities = array();
				}
				
				if(($replace_username_stat > 0) && ($enable_text_message == 0) && (strlen($message) > 0))
				{
					preg_match_all('(@\w*)', $message, $list_s); 
					if(count($list_s[0]) > 0)
					{
						foreach($list_s[0] as $n=>$val)
						{
							$message = str_replace($val,$replace_username,$message);
						}
					}
				}
				
				if(($word_send_post_type == 0) && ($word_send_post_func == 1) or ($word_send_post_func == 0))
				{
					switch($ignore_post_type)
					{
						case 1:
							if((empty($message)) && (isset($update['message']['media']['_']))){ return false; }
							break;
						case 2:
							if(!empty($message)){ return false; }
							break;	
						case 3:	
							if((!empty($message)) && (!isset($update['message']['media']['_']))){ return false; }
							break;
						case 4:
							if((!empty($message)) && (isset($update['message']['media']['_']))){ return false; }
							break;
						case 5:
							if(empty($message)){ return false; }
							break;
						case 6:
							if((!empty($message)) && (isset($update['message']['media']['_']))){ return false; }
							break;
						case 7:
							if((!isset($update['message']['media']['_'])) or
								($update['message']['media']['_'] !== 'messageMediaDocument') &&
								(!isset($update['message']['media']['document']['_'])) &&
								($update['message']['media']['_'] !== 'messageMediaWebPage')){ return false; }
							break;
						case 8:
							if((!isset($update['message']['media']['_'])) or
								($update['message']['media']['_'] !== 'messageMediaPhoto') &&
								(!isset($update['message']['media']['webpage']['photo']['_']))){ return false; }
							break;
						case 9:
							if((isset($update['message']['media']['document']['mime_type'])) &&
								(stristr($update['message']['media']['document']['mime_type'],'video/'))){ return false; }
							break;
						case 10:
							if(isset($update['message']['media'])){ $message = ''; }	
					}
				}
				
				if(mb_strlen($sugn_channel) > 0)
				{
					$sugn_val = $sugn_channel;
					$sugn_text = unicode_char($sugn_val);
					$sugn_text = htmlspecialchars_decode($sugn_text);
					$sugn_x = '';
					
					if(strstr($sugn_text,'|'))
					{ 
						$sugn_text_arr = explode("\n",$sugn_text);
						$sugn_text = explode("|",$sugn_text);
						$sugn_channel = $sugn_text[array_rand($sugn_text)];
						
						foreach($sugn_text_arr as $v)
						{
							if(empty($v)){ $sugn_x .= "\n"; }
							else{ $sugn_x = str_replace($v,$sugn_channel,$sugn_val); }
						} 
					}else { $sugn_x = $sugn_text; }
					
					$message .= $sugn_x;
				}
				
				
	# END FILTER CODE
			
						
			if($user_data['limit_status'] == 1)
			{
				$limit_self = $user_data['limit_self'];
				$hours = time() - $user_data['limit_time'];
				$hours = round($hours / 3600);
			
				if($hours >= $user_data['limit_hours'])
				{
					mysqli_query($db,"UPDATE `channels_settings` SET `limit_time` = '".time()."',`limit_self` = '0' 
																						WHERE `id` = '$setting_id'");
					$hours = 0;
					$limit_self = 0;
				}	

				if(($hours > $user_data['limit_hours']) or ($limit_self >= $user_data['limit_post'])){ return false; }
				
				if($limit_self <= $user_data['limit_post'])
				{
					$limit_self++;
					mysqli_query($db,"UPDATE `channels_settings` SET `limit_self` = '$limit_self' WHERE `id` = '$setting_id'");
				}
			}
			
			$channel_id = $update['message']['to_id']['channel_id'];
			$r = mysqli_query($db,"SELECT `id`,`channel_publish_id`,`channel_publish`,`all_post`,`status`,`setting_id` 
										FROM `channels_origin` WHERE `channel_origin_id` = '$channel_id' AND `account_id` = '$id_account'");
			$num = mysqli_num_rows($r);
			if($num == 0){ return false; }
		
	
			while($data_x = mysqli_fetch_assoc($r))
			{
				try
				{
					if(!isset($data_x['channel_publish_id'])){ continue; }
					if((isset($data_x['status'])) &&($data_x['status'] == 1)){ continue; }
					$channel_publish = $data_x['channel_publish'];
					$channel_id_to = '-100'.$data_x['channel_publish_id'];
					$task_id = $data_x['id'];
					$all_post = $data_x['all_post'];
					$setting_id = $data_x['setting_id'];
					$duplicat_dis = get_setting_channel('duplicat_dis',$setting_id);
					
					if((!empty($message)) && ($duplicat_dis == 0))
					{
						$hash = md5($message);
						$rx = mysqli_query($db,"SELECT `hash` FROM `duplicatus_post` WHERE `hash` = '$hash' AND `setting_id` = '$task_id'");
						$ex = mysqli_num_rows($rx);
						if(($ex > 0) && ($enable_text_message == 0)){ continue; }
						mysqli_query($db,"INSERT INTO `duplicatus_post` (`hash`,`setting_id`) VALUES ('$hash','$task_id')");
					}
					
					$reply_to = get_setting_channel('reply_post',$setting_id);
					if($reply_to == 1)
					{
						if(isset($update['message']['reply_to']['reply_to_msg_id'])){ continue; }
					}
					
					$length_message_limit = get_setting_channel('length_message_limit',$setting_id);
					if($length_message_limit == 1)
					{
						$length = get_setting_channel('length_message',$setting_id);
						$type_check_m = get_setting_channel('type_check_m',$setting_id);
						if(!isset($type_check_m)){ $type_check_m = 0; }
						$type_check_m = intval($type_check_m);
						if(($length > 0) && ($type_check_m > 0))
						{
							if(($type_check_m == 1) && (mb_strlen($message) < $length)){ return false; }
							elseif(($type_check_m == 2) && (mb_strlen($message) > $length)){ return false; }
						}
					}
					
					$rx = mysqli_query($db,"SELECT `timetable`,`time_post`,`forward_message`,`schedule`,`schedule_date`,`send_text_no_media`,
																	`post_no_webpage` FROM `channels_settings` WHERE `id` = '$setting_id'");
					$data_channel = mysqli_fetch_assoc($rx);
				
					$schedule = intval($data_channel['schedule']);
					$schedule_date = intval($data_channel['schedule_date']);
					if(strstr($data_channel['schedule_date'],'-'))
					{
						$schedule_arr = explode('-',$data_channel['schedule_date']);
						$schedule_date = $schedule_arr[array_rand($schedule_arr)];
					}
					
					if(empty($schedule_date)){ $schedule_date = 0; }
					if(($schedule_date > 0) && ($schedule == 1))
					{
						$schedule_date = strtotime("+$schedule_date hour",time());
					}else { $schedule_date = 0; }
						
					$forward_message = intval($data_channel['forward_message']);
			
					if(($data_channel['timetable'] == 1) && (!empty($data_channel['time_post'])))
					{
						if(!mb_strstr($data_channel['time_post'],date('H'))){ continue; }
					}
					
					$send_text_no_media = intval($data_channel['send_text_no_media']);
					if($data_channel['post_no_webpage'] == 1){ $no_webpage = true; } else { $no_webpage = false; }

					if((isset($update['message']['media']['_'])) && ($send_text_no_media == 0))
					{
						$type = $update['message']['media']['_'];
						
						$file_ = '';
						if($type === 'messageMediaDocument')
						{
							if($forward_message == 1){						
								yield $this->messages->forwardMessages(['grouped'=>false,'from_peer'=> $update,'id' =>[$update['message']['id'],],
																											'to_peer' => $channel_id_to, ]);
							}else
							{
								$inputDocument = ['_' => 'inputDocument', 'id' => $update['message']['media']['document']['id'], 
													'access_hash' => $update['message']['media']['document']['access_hash'], 
													'file_reference' => $update['message']['media']['document']['file_reference'],
												];
					
								$recv = yield $this->messages->sendMedia([
									'peer' => $channel_id_to,
									'media' => [
										'_'=>'inputMediaDocument',
										'id' => $inputDocument,
									],
									'message' => $message,
									'thumb' => $thumbs,
									'entities'=>$entities,
									'schedule_date'=>$schedule_date,
									'parse_mode' => 'HTML',
								]);
								
								if($recv['updates'][0]['_'] === 'updateMessageID')
								{
									$message_id1 = $update['message']['id'];
									$message_id2 = $recv['updates'][0]['id'];
									
									$r = mysqli_query($db,"SELECT `id` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
															AND `account_id` = '$id_account' AND `channel_id_from` = '$channel_id'");
									$num = mysqli_num_rows($r);
									if($num == 0)
									{
										mysqli_query($db,"INSERT INTO `update_messages` (`message_id1`,`message_id2`,`channel_id_from`,
																										`channel_id_to`,`account_id`) 
														VALUES ('$message_id1','$message_id2','$channel_id','$channel_id_to','$id_account')");
									}
								}
							}	
							$all_post++;
						}elseif($type === 'messageMediaPhoto')
						{
							$enable_bot_vote = get_setting_channel('enable_bot_vote',$setting_id);
							if($enable_bot_vote == 1)
							{
								if(empty($file_id))
								{
									$file = $path.download_dir.'/'.time().'.jpg';
									yield $this->downloadToFile($update['message']['media'], $file);
								}
								$file_id = send_vote($channel_id_to,$message,$file,$data_x['channel_publish_id'],$file_id);
							}else
							{
								if($forward_message == 1){						
									yield $this->messages->forwardMessages(['grouped'=>false,'from_peer'=> $update,
											'id' =>[$update['message']['id'],],'to_peer' => $channel_id_to,'schedule_date'=>$schedule_date, ]);
								}else
								{
									try
									{
										$inputPhoto = ['_' => 'inputPhoto', 'id' => $update['message']['media']['photo']['id'], 
															'access_hash' => $update['message']['media']['photo']['access_hash'], 
															'file_reference' => $update['message']['media']['photo']['file_reference'],
														];
										$media = [
												'_' => 'inputMediaPhoto',
												'id' => $inputPhoto,
											];
										
										$recv = yield $this->messages->sendMedia([
											'peer' => $channel_id_to,
											'media' => $media,
											'message' => $message,
											'entities'=>$entities,
											'schedule_date'=>$schedule_date,
											'parse_mode' => 'HTML'
										]);
										
										if($recv['updates'][0]['_'] === 'updateMessageID')
										{
											$message_id1 = $update['message']['id'];
											$message_id2 = $recv['updates'][0]['id'];
											
											$r = mysqli_query($db,"SELECT `id` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
																AND `account_id` = '$id_account' AND `channel_id_from` = '$channel_id'");
											$num = mysqli_num_rows($r);
											if($num == 0)
											{
												mysqli_query($db,"INSERT INTO `update_messages` (`message_id1`,`message_id2`,`channel_id_from`,
																												`channel_id_to`,`account_id`) 
															VALUES ('$message_id1','$message_id2','$channel_id','$channel_id_to','$id_account')");
											}
										}
									}catch (\danog\MadelineProto\RPCErrorException $e){ 
											$error = $e->getMessage();  }	
								}
							}
							$all_post++;
						}elseif($type === 'messageMediaWebPage')
						{
							if($forward_message == 1){						
								yield $this->messages->forwardMessages(['grouped'=>false,'from_peer'=> $update,'id' =>[$update['message']['id'],],
																				'to_peer' => $channel_id_to,'schedule_date'=>$schedule_date, ]);
							}else
							{
								if(!isset($update['message']['media']['webpage']['url'])){ return false; }
								$url = $update['message']['media']['webpage']['url'];
								
								if(isset($update['message']['media']['webpage']['title']))
								{
									$title = $update['message']['media']['webpage']['title'];
									if(empty($message)){ $message = '<a href="'.$url.'">'.$title.'</a>'; }
								}else { $message = $message.'<a href="'.$url.'">⁣</a>'; }
								
								$recv = yield $this->messages->sendMessage(['peer' => $channel_id_to, 'message' => $message,'parse_mode'=>'HTML',
																			'entities'=>$entities,'schedule_date'=>$schedule_date,
																			'no_webpage'=>$no_webpage, ]);
								
								if($recv['updates'][0]['_'] === 'updateMessageID')
								{
									$message_id1 = $update['message']['id'];
									$message_id2 = $recv['updates'][0]['id'];
									
									$r = mysqli_query($db,"SELECT `id` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
															AND `account_id` = '$id_account' AND `channel_id_from` = '$channel_id'");
									$num = mysqli_num_rows($r);
									if($num == 0)
									{
										mysqli_query($db,"INSERT INTO `update_messages` (`message_id1`,`message_id2`,`channel_id_from`,
																										`channel_id_to`,`account_id`) 
														VALUES ('$message_id1','$message_id2','$channel_id','$channel_id_to','$id_account')");
									}
								}
							}
							$all_post++;
						}
						
					}
					else
					{	
						if($forward_message == 1){				
							yield $this->messages->forwardMessages(['grouped'=>false,'from_peer'=> $update,'id' =>[$update['message']['id'],],
																			'to_peer' => $channel_id_to,'schedule_date'=>$schedule_date, ]);
						}else{
							$recv = yield $this->messages->sendMessage(['peer' => $channel_id_to, 'message' => $message,'parse_mode'=>'HTML',
																				  'entities'=>$entities,'schedule_date'=>$schedule_date,
																				  'no_webpage'=>$no_webpage,]);
							
							if($recv['updates'][0]['_'] === 'updateMessageID')
							{
								$message_id1 = $update['message']['id'];
								$message_id2 = $recv['updates'][0]['id'];
								
								$r = mysqli_query($db,"SELECT `id` FROM `update_messages` WHERE `message_id1` = '$message_id1' 
														AND `account_id` = '$id_account' AND `channel_id_from` = '$channel_id'");
								$num = mysqli_num_rows($r);
								if($num == 0)
								{
									mysqli_query($db,"INSERT INTO `update_messages` (`message_id1`,`message_id2`,`channel_id_from`,
																									`channel_id_to`,`account_id`) 
													VALUES ('$message_id1','$message_id2','$channel_id','$channel_id_to','$id_account')");
								}
							}
						}
						$all_post++;										
					}
				}catch (\danog\MadelineProto\RPCErrorException $e) { }
			
				$file_id = 0;
				mysqli_query($db,"UPDATE `channels_origin` SET `all_post` = '$all_post' WHERE `id` = '$task_id' AND `account_id` = '$id_account'");
				
				if(file_exists($file)){ unlink($file); }
			}
				}
		}
		public function onUpdateNewMessage(array $update): \Generator
		{
			if ($update['message']['_'] === 'messageEmpty' || $update['message']['out'] ?? false) {
				return false;
			}

			global $db;
			global $id_account;
			
			$status = get_setting_account('status',$id_account);
			if($status == 1){ exit; }
		
		}
	}


	$profile = get_setting_account('profile_tg',$id_account);	
	$profile = $path.root_dir.'/accounts/'.$profile;
	try
	{		
		$settings['app_info']['api_id'] = 2834;
		$settings['app_info']['api_hash'] = '68875f756c9b437a8b916ca3de215815';
		$settings['logger']['logger'] = 0;
		$settings['ipc']['slow'] = true;
		$settings['upload']['allow_automatic_upload'] = false;
		
		$settings['connection_settings']['all']['protocol'] = 'tcp_full';
		$settings['connection_settings']['all']['proxy'] = '\Socket';
		$settings['connection_settings']['all']['proxy_extra'] = ['address' => '','port' =>''];

		$API = new \danog\MadelineProto\API($profile, $settings);
		$API->startAndLoop(EventHandlerPoster::class);
	}catch(\danog\MadelineProto\RPCErrorException $e) { echo $e->getMessage();exit; } 
?>