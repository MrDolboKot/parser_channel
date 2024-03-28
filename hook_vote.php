<?php
	include('functions.php');

    $content = file_get_contents("php://input");
    $update = json_decode($content, true);
    $message = $update["message"];
	$id_chat = $message["chat"]["id"]; 
    $text = $message["text"];
	$user = $message['from']['first_name'];

	if(!empty($update['callback_query']['data']))
	{
		$callback_query_id = $update['callback_query']['id'];
		$cmd_id = $update['callback_query']['data'];
		$id_chat = $update['callback_query']['from']['id'];
		$user = $update['callback_query']['from']['first_name'].' '.$update['callback_query']['from']['last_name'];
	
		$token = get_setting('bot_token');
		$api_bot_url = "https://api.telegram.org/bot".$token;
	
		if(strstr($cmd_id,'/rating:'))
		{
			list($com,$plus,$minus,$type,$message_id,$id_post) = explode(':',$cmd_id);
			
			$r = mysqli_query($db,"SELECT `id` FROM `vote_channels_exits` WHERE `id_chat` = '$id_chat' AND `id_post` = '$id_post'");
			$num = mysqli_num_rows($r);

				if($num == 0)
				{
					mysqli_query($db,"INSERT INTO `vote_channels_exits` (`id_chat`,`id_post`,`type`) VALUES ('$id_chat','$id_post','')");
				}else
				{
					$content_ = array(
						'callback_query_id' => $callback_query_id,
						'show_alert'=>false,
						'text' => "❗️ You have already voted",
						);
					
					$url_ = "https://api.telegram.org/bot$token/answerCallbackQuery?";
					file_get_contents($url_.http_build_query($content_));
					exit;
				}
			
				$r1 = mysqli_query($db,"SELECT `plus`,`minus`,`channel_id`,`message` FROM `vote_channels` WHERE `id` = '$id_post' ");
				$data_post = mysqli_fetch_assoc($r1);
				$type_content = $data_post['type'];
				if(intval($data_post['plus']) <> $plus){ $plus = $data_post['plus']; }
				if(intval($data_post['minus']) > $minus){ $minus = $data_post['minus']; }
				$channel_id = '-100'.$data_post['channel_id'];
	
				if($type == '+'){ $plus++; }elseif($type == '-'){ $minus++; }
				
				$plus = intval($plus);
				$minus = intval($minus);
					
				$buttons_inline['inline_keyboard'][0][0]['text'] = "👍 $plus";
				$buttons_inline['inline_keyboard'][0][0]['callback_data'] = "/rating:$plus:$minus:+:$message_id:$id_post";
				$buttons_inline['inline_keyboard'][0][1]['text'] = "👎 $minus";
				$buttons_inline['inline_keyboard'][0][1]['callback_data'] = "/rating:$plus:$minus:-:$message_id:$id_post";
						
				$markup = json_encode($buttons_inline);
				$content = array(
						'chat_id' => $channel_id,
						'message_id'=>$message_id,
						'reply_markup' => $markup,
						'caption' => unicode_char($data_post['message']),
						'parse_mode'=>'HTML',
					);

				$data = file_get_contents($api_bot_url.'/editMessageCaption?'.http_build_query($content));
			
				mysqli_query($db,"UPDATE `vote_channels` SET `plus` = '$plus', `minus` = '$minus' WHERE `id` = '$id_post'");
				mysqli_query($db,"UPDATE `vote_channels_exits` SET `type` = '$type' WHERE `id_post` = '$id_post' AND `id_chat` = '$id_chat'");
				
		}

	}
?>