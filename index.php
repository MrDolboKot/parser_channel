<?php
	include('functions.php');
	global $db;
	if(!$session){ header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir.'/login.php');exit; }

	$act = strip_tags($_GET['act']);
	preg_match("/^[a-zA-Z0-9\w]+/", $act,$actx);
	$act = $actx[0];
	
	if(isset($_GET['file']))
	{
		$file = intval($_GET['file']);
		$file = $_SERVER['DOCUMENT_ROOT'].download_dir.'/'.$file.'.dump';
		if(file_exists($file)){ to_download($file); }
		exit;
	}

	if($act == 'logout')
	{
		foreach($_SESSION as $p=>$x)
		{
			unset($_SESSION[$p]);
		}
		
		 header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir.'/login.php');
		 exit;
	}	
 
	$self_account_id = get_setting('self_account_id');
	$pname = get_setting_account('profile_tg',$self_account_id);

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<title>Автопостер контента. v4.4 / KostolomVers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="@suicide_vll">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	 <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
     <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	 
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="/parser_channel/js/func.js"></script>

	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.js"></script>
	
	<link href="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdn.jsdelivr.net/npm/select2@4.0.12/dist/js/select2.min.js"></script>
	
	<link rel="stylesheet" href="/parser_channel/css/font-awesome.css">
	<link rel="stylesheet" href="/parser_channel/css/style.css">
  </head>
  <body>
 <div class="container">
 
	<input type='hidden' id='page_x' value='<?=$_SERVER['REQUEST_URI'];?>'>
	<input type='hidden' id='select2_open' value='0'>
	<ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link active" data-toggle="tab" href="#power"><i class="fa fa-paper-plane-o" ></i> Управление каналами</a>
    </li>
    <li class="nav-item update_channels">
      <a class="nav-link" data-toggle="tab" href="#setting_channel"><i class="fa fa-list" ></i> Настройки фильтров каналов</a>
    </li>
	<li class="nav-item">
		<a class="nav-link" data-toggle="tab" href="#setting"><i class="fa fa-cogs" ></i> Общие настройки</a>
	</li>
	<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#dump_opt"><i class="fa fa-files-o" ></i> Рез.копия</a>
		</li>
  </ul>
	
	 <div class='col-md-12'>

		  <div class="tab-content">
			<div id="power" class="tab-pane active" style='padding:0;'>
			  <div class='col-md-12' style='padding:0;'>
			  <div class='col-md-4' style='margin-bottom:5px;padding:0;'>	
					<label><b>Канал источник:</b></label>					
					<select class='select2 list_channel_origin' style='width:100%;'>
					<option value=''></option>
					<?
						$rx = mysqli_query($db,"SELECT `channel_url`,`name` FROM `join_exists` WHERE `type` = '1' AND `name` != '' 
																									AND `id_account` = '$self_account_id'");
						$num1 = mysqli_num_rows($rx);
						if($num1 == 0){ $view_o = 'display'; } else { $view_o = 'none'; }
						while($list = mysqli_fetch_assoc($rx))
						{
					?>
						<option value='<?=$list['channel_url'];?>'>🟢 <?=html_entity_decode($list['name']);?></option>
						<?}?>
					<option value='0'>Указать другой канал<option>
					</select>
					
					<input type="text" class="form-control form-control-sm" id='channel_origin' style='margin:0 0 2px;;display:<?=$view_o;?>;' placeholder='https://t.me/username или id канала' title='https://t.me/channel \ https://t.me/joinchat/XXXX или id канала формата 1138327XXX'>
				</div>	
			  
				<div class='col-md-5'>	
					<label><b>Канал публикации:</b></label>
					<select class='select2 list_channel_publish' style='width:100%;'>
					<option value=''></option>
					<?
						$rx = mysqli_query($db,"SELECT `channel_url`,`name` FROM `join_exists` WHERE `type` = '2' AND `name` != ''
																									AND `profile_tg` = '$pname'");
						$num1 = mysqli_num_rows($rx);
						if($num1 == 0){ $view_p = 'display'; } else { $view_p = 'none'; }
						while($list = mysqli_fetch_assoc($rx))
						{
					?>
						<option value='<?=$list['channel_url'];?>'>🟢 <?=html_entity_decode($list['name']);?></option>
						<?}?>
					<option value='0'>Указать другой канал<option>
					</select>
					
					<input type="text" class="form-control form-control-sm" id='channel_publish' style='margin-top:2px;display:<?=$view_p;?>;' placeholder='https://t.me/joinchat/XXXXXXXXX'>
				</div>	
		
				<div class='col-md-2'>	
					
					<br><button class='btn add_channel' ><i class="fa fa-plus" ></i> Добавить</button>
					<img src='<?=root_dir;?>/img/load.gif' id='load' style='display:none;'>
				</div>	
				<?
					$r = mysqli_query($db,"SELECT `id`,`channel_origin`,`channel_publish`,`status`,`all_post`,`publish_name`,
											`origin_name`,`setting_id` FROM `channels_origin` WHERE `account_id` = '$self_account_id'");
					$all = mysqli_num_rows($r);
				?>
				<br><label><b>Список задач: <?=$all;?></b></label>
				<table class="table table-bordered table-striped table_x1">
				  <thead>
					<tr>
					  <th scope="col" class='opt_col' style='width:36%;'>Канал публикации</th>
					  <th scope="col" class='opt_col invisible_' style='width:36%;'>Источник</th>
					  <th scope="col" class='opt_col '>Всего постов</th>
					  <th scope="col" class='opt_col invisible_'>Дейсвие</th>
					  <th scope="col" class='opt_col' style='width:41px;'>#</th>
					</tr>
				  </thead>
				  <tbody id='channels'>
				  <?
					
					while($list = mysqli_fetch_assoc($r))
					{
						if($list['status'] == 0){ $btn1 = ''; $btn2 = "style='display:none;'"; } 
												else{$btn1 = "style='display:none;'"; $btn2 = ''; }					
				  ?>
					<tr class='item<?=$list['id'];?>'>
						<td class='invisible_'>
						<select class='select2 change_channel_publish' style='width:100%;' object_id='<?=$list['id'];?>'>
						<option value='0'>-</option>
						<?							
							$rx1 = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`ready` FROM `join_exists` WHERE `type` = '2' 
																							AND `profile_tg` = '$pname'");
					
							while($list_p = mysqli_fetch_assoc($rx1))
							{
								if($list_p['channel_url'] == $list['channel_publish']){ $sel = 'selected'; } else { $sel = ''; }
								
								$name_ch = unicode_char($list_p['name']);
								if(strlen($name_ch) > 50){ $name_ch = substr($name_ch,0,50); }
								if($list_p['ready'] == 1){ $name_ch = "❗️ $name_ch - Отсуствует в аккаунте";$dis = 'disabled'; }else { $dis = ''; }
						?>
							<option value='<?=$list_p['id'];?>' <?=$dis?> <?=$sel;?>><?=$name_ch?></option>
						<?}?>
						</select>
						
						</td>
						<td>
						<select class='select2 change_channel_origin' style='width:100%;' object_id='<?=$list['id'];?>'>
						<option value='0'>-</option>
						<?
							$rx2 = mysqli_query($db,"SELECT `id`,`name`,`channel_url`,`ready` FROM `join_exists` WHERE `type` = '1' 
																							AND `profile_tg` = '$pname'");
							while($list_o = mysqli_fetch_assoc($rx2))
							{
								if($list_o['channel_url'] == $list['channel_origin']){ $sel = 'selected'; } else { $sel = ''; }
								
								$name_ch = unicode_char($list_o['name']);
								if(strlen($name_ch) > 50){ $name_ch = substr($name_ch,0,50); }
								if($list_o['ready'] == 1){ $name_ch = "❗️ $name_ch - Отсуствует в аккаунте";$dis = 'disabled'; }else { $dis = ''; }
						?>
							<option value='<?=$list_o['id'];?>' <?=$dis?> <?=$sel;?>><?=$name_ch?></option>
						<?}?>
						</select>
						
						</td>
						<td class='invisible_ center'><?=$list['all_post'];?></td>
						<td class='center'>
						<button class="btn btn-primary btn_posted stop stop<?=$list['id'];?>" <?=$btn1;?> value='<?=$list['id'];?>'><i class="fa fa-stop" ></i> Стоп</button>
							<button class="btn btn-primary btn_posted start start<?=$list['id'];?>" <?=$btn2;?> value='<?=$list['id'];?>'><i class="fa fa-play"  ></i> Старт</button>
					
						</td>
						<td class='center'><i class="fa fa-trash del_channel" aria-hidden="true" value='<?=$list['id'];?>' setting_id='<?=$list['setting_id'];?>'></i></td>
					</tr>
					<?}?>
				  </tbody>
				  </table>
				
			  </div>
			</div>
			
	<div id="setting" class="container tab-pane fade" >
		<div class='col-md-12' style='padding:0;margin-top:-22px;'>	
		
		<div class="col-md-11 clear_p" >
			<label><b>Текущий аккаунт:</b></label>
			<select class='select2' id='self_account' style='width:100%;margin-bottom:6px;'>
			<?
				$r = mysqli_query($db,"SELECT `id`,`profile_tg` FROM `accounts_setting`");
				while($list = mysqli_fetch_assoc($r))
				{
					if($list['id'] == $self_account_id){ $sel = 'selected'; } else { $sel = ''; }
					?><option value='<?=$list['id'];?>' <?=$sel;?>><?=$list['profile_tg'];?></option><?						
			?>
			
				<?}?>
			</select>
			
			<button type="button" class="btn btn-primary btn-xs change_account"><i class="fa fa-sign-out" aria-hidden="true"></i> Переключить</button>
			<button type="button" class="btn btn-danger btn-xs del_account" style='float:right;'><i class="fa fa-remove" aria-hidden="true"></i> Удалить</button>
			
			
		</div>
		
			 <div class='col-md-5 clear_p'>
			 <?	
				$rx = mysqli_query($db,"SELECT `bot_token`,`status`,`restart` FROM `accounts_setting` WHERE `id` = '$self_account_id'");
				
				$data_x = mysqli_fetch_assoc($rx);
				$bot_token = $data_x['bot_token'];
				$status = $data_x['status'];
				$restart = intval($data_x['restart']) ? :'';
			 ?>
			 <div class="form-group">
				<label><b>Статус работы:</b></label>
				
				<div class="form-check-inline">
				<div class="custom-control custom-checkbox col-sm-12">
				  <input type="radio" class="custom-control-input status_bot" name="status_bot" id='status_bot1' value='0' <?if($status == 0){ echo 'checked'; }?>>
				  <label for='status_bot1' class="custom-control-label">Вкл</label>
				</div>
			
				</div>
				<div class="form-check-inline">
				<div class="custom-control custom-checkbox col-sm-12">
				  <input type="radio" class="custom-control-input status_bot" name="status_bot" id='status_bot2' value='1' <?if($status == 1){ echo 'checked'; }?>>
				  <label for='status_bot2' class="custom-control-label">Выкл</label>
				</div>
				</div>
				
				<hr>
				<h6>Переименовать Канал:</h6>
				<label><b>Выберите канал(сменится в панели и telegram):</b></label>
				<select class='select2' id='rename_channel' style='width:100%;margin-bottom:6px;'>
				<option value='0'></option>
				<?
					$r = mysqli_query($db,"SELECT `channel_id`,`name` FROM `join_exists` WHERE `type` = '2' AND `profile_tg` = '$pname'");
					while($list = mysqli_fetch_assoc($r))
					{
						?><option value='<?=$list['channel_id'];?>' ><?=$list['name'];?></option><?						
				?>
				
					<?}?>
				</select>
				<span id='new_name' style='display:none;'>
					<br><label><b>Новое название Канала:</b></label>
					<input type="text" class="form-control form-control-sm" id='channel_name'>
					
					<button type="button" class="btn btn-primary btn-xs change_name_channel"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Сменить</button>
				</span><br>
				
				<br><label><b>Bot token(Like inline image):</b></label>
				<input type="text" class="form-control form-control-sm" id='bot_token' value='<?=$bot_token;?>' title='Для работы требуется SSL DOMAIN'>
				
				<br>
				<label><b>Авто-Рестарт каждые(мин):</b></label>
				<input type="number" class="form-control form-control-sm" id='restart' value='<?=$restart;?>' min='0' max='60' placeholder='0 - откл, рекоменд - 30мн' title='Только в случаях когда посты с источника приходят редко.'>
				
				<hr>
				<button class='btn btn-primary save_setting' style='width:100%;'><i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить</button><p>
			</div>	
			</div>
		
			 <div class='col-md-5 clear_p'>
					<label><b>Смена пароля:</b></label>
					<input type="password" class="form-control form-control-sm" id='new_pwd' >
					<button type="button" class="btn btn-primary btn-xs change_pwd"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> Сменить</button>
					<a href='<?=root_dir;?>/logout/' class='btn btn-primary btn-xs'><i class="fa fa-sign-out" aria-hidden="true"></i> Завершить сессию</a>
					
					
			  </div>
			 
			
		
	  </div>
			  
	</div>
	
	<div id="setting_channel" class="container tab-pane fade" >
		<div class='col-md-12' style='padding:0;'>	
		
		<?
			$r1 = mysqli_query($db,"SELECT `setting_id` FROM `channels_origin` WHERE `account_id` = '$self_account_id' ORDER BY `id` LIMIT 0,1");
			$data_s = mysqli_fetch_assoc($r1);
			$setting_id_1 = $data_s['setting_id'];

			$rx = mysqli_query($db,"SELECT `spam_filter`,`replace_words`,`filter_message`,`filter_inline`,`enable_text_message`,`my_text_message`,
									`replace_link`,`skip_text`,`enable_bot_vote`,`filter_links`,`timetable`,`time_post`,`limit_post`,`limit_hours`,
									`limit_time`,`limit_status`,`forward_message`,`ignore_post_type`,`sugn_channel`,`word_send_post`,
									`word_send_post_func`,`word_send_post_type`,`replace_username_stat`,`replace_username`,`update_message`, 
									`delete_message`,`reply_post`,`length_message_limit`,`length_message`,`type_check_m`,
									`schedule`,`schedule_date`,`send_text_no_media`,`post_no_webpage`,`ignore_album`,`ignore_audio`,`duplicat_dis` 
																					FROM `channels_settings` WHERE `id` = '$setting_id_1'");
					
			$data_x = mysqli_fetch_assoc($rx);
			$enable_bot_vote = $data_x['enable_bot_vote'];
			$spam_filter = $data_x['spam_filter'];
			$replace_words = $data_x['replace_words'];
			$filter_message = $data_x['filter_message'];
			$filter_inline = $data_x['filter_inline'];
			$enable_text_message = $data_x['enable_text_message'];
			$my_text_message = $data_x['my_text_message'];
			$replace_link = $data_x['replace_link'];
			$skip_text = $data_x['skip_text'];
			$filter_links = $data_x['filter_links'];
			$timetable = $data_x['timetable'];
			$limit_status = $data_x['limit_status'];
			$forward_message = $data_x['forward_message'];
			$ignore_post_type = $data_x['ignore_post_type'];
			$sugn_channel = $data_x['sugn_channel'];
			$word_send_post = $data_x['word_send_post'];
			$word_send_post_func = $data_x['word_send_post_func'];
			$word_send_post_type = $data_x['word_send_post_type'];
			$replace_username_stat = $data_x['replace_username_stat'];
			$replace_username = $data_x['replace_username'];
			$update_message = $data_x['update_message'];
			$delete_message = $data_x['delete_message'];
			$reply_post = $data_x['reply_post'];
			$length_message_limit = $data_x['length_message_limit'];
			$type_check_m = $data_x['type_check_m'];
			$schedule = $data_x['schedule'];
			$schedule_date = $data_x['schedule_date'];
			$send_text_no_media = $data_x['send_text_no_media'];
			$post_no_webpage = $data_x['post_no_webpage'];
			$ignore_album = $data_x['ignore_album'];
			$ignore_audio = $data_x['ignore_audio'];
			$duplicat_dis = $data_x['duplicat_dis'];
			
			$sel = 'selected';
			if($timetable == 1){ $timetable_box = 'block;'; } else { $timetable_box = 'none;'; }
			if($limit_status == 1){ $limit_box = 'block;'; } else { $limit_box = 'none;'; }
			if($word_send_post_func == 1){ $word_send_x = 'block;'; } else { $word_send_x = 'none;'; }
			if($length_message_limit == 1){ $length_message_limit_x = 'block;'; } else { $length_message_limit_x = 'none;'; }
		?>
		
		<div class='col-md-11' style='padding:2px 0 16px 15px'>
			<label><i class="fa fa-bars" aria-hidden="true"></i> <b>Выберите нужный канал для настройки фильтров:</b></label>
			<img src='<?=root_dir;?>/img/load.gif' id='load_setting' style='display:none;'>
			<select id='channel_setting' class='select2 channel_setting' style='width:98.6%;'>
			<?
				$r = mysqli_query($db,"SELECT `setting_id`,`origin_name`,`publish_name` FROM `channels_origin` WHERE `account_id` = '$self_account_id'");
				while($list = mysqli_fetch_assoc($r))
				{
			?>
				<option value='<?=$list['setting_id']?>'>Источник: <?=$list['origin_name']. " | Канал публикации: ".$list['publish_name'];?></option>
				<?}?>
			</select>
		</div>
		
		 <div class='col-md-5 load_data'>
			<label desc='spam_filter'><b>Фильтр(стоп слова):</b></label>
			<textarea id='spam_filter' class='form-control save_opt_tx' placeholder='Укажите стоп-слова'><?=$spam_filter;?></textarea>
			
			<label desc='replace_words'><b>Удалять слова(слово|замена) или <span title='Удалить\Заменить 3 слова. Пример: слово *Dark *Word'>(слово * *|замена)</span>:</b></label>

			<textarea id='replace_words' class='form-control save_opt_tx' placeholder='Укажите слова которые нужно удалять\заменять'><?=$replace_words;?></textarea>
				
			<label desc='filter_links'><b>Удалять из поста указанные ссылки:</b></label>
			<?
				if(($filter_message == 4) or ($filter_message == 5)){ $dis_links = 'disabled';$link_bg = 'style="background:#f1f1f1"'; } 
				else { $dis_links = '';$link_bg = 'style="background:##f9f9f9"'; }
			?>
				<textarea id='filter_links' class='form-control save_opt_tx' placeholder='Укажите ссылки которые нужно удалять' <?=$dis_links;?> <?=$link_bg;?>><?=$filter_links;?></textarea>	
			
			<label desc='sugn_channel'><b>Подпись канала(каждом посте снизу):</b></label>
			<textarea id='sugn_channel' class='form-control save_opt_tx' placeholder='Укажите 🔱 текст подписи, или ссылку на ваш канал. Sugn1|Sugn2|Sugn3|Sugn4 - Рандом выборка'><?=$sugn_channel;?></textarea>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input word_send_post_func" id='word_send_post_func' <?if($word_send_post_func == 1){ echo 'checked'; }?>>
				  <label for='word_send_post_func' class="custom-control-label"><b>Публиковать только при наличии одного из слов</b></label>
				</div>
			</div>
			
			<div id='box_word_send' style='display:<?=$word_send_x;?>'>
				<select id='word_send_post_type' class='select2' style='width:100%;'>
					<option value='0' <?if($word_send_post_type == 0){ echo $sel; }?>>С учетом фильтров</option>
					<option value='1' <?if($word_send_post_type == 1){ echo $sel; }?>>В обход фильтров</option>
				</select><p>
				
				<textarea id='word_send_post' class='form-control' style='margin-top:11px;' title='При наличии указанных слов, пост будет опубликован' ><?=$word_send_post;?></textarea>
			</div>
			
		  </div>
			 
			 <div class='col-md-5 load_data' style='padding:0;'>
			 <label><b>Фильтрация контента</b></label>
			<select id='filter_message' class='select2' style='width:100%;'>
				<option value='0' <?if($filter_message == 0){ echo $sel; }?>>Не определен</option>
				<option value='1' <?if($filter_message == 1){ echo $sel; }?>>Удалять все ссылки в сообщении</option>
				<option value='2' <?if($filter_message == 2){ echo $sel; }?>>Удалять ссылки только t.me/username* , t.me/joinchat/XXXX* и @Username*</option>
				<option value='3' <?if($filter_message == 3){ echo $sel; }?>>Игнорировать при наличии ссылок c t.me/name* и t.me/joinchat/XXXX*</option>
				<option value='4' <?if($filter_message == 4){ echo $sel; }?>>Игнорировать наличие любых ссылок</option>
				<option value='5' <?if($filter_message == 5){ echo $sel; }?>>Заменять все ссылки в сообщении на свою(указать)</option>
			</select>
			
			
			<label><b>Доп фильтрация</b></label>
			<select id='ignore_post_type' class='select2' style='width:100%;'>
				<option value='0' <?if($ignore_post_type == 0){ echo $sel; }?>>Не определен</option>
				<optgroup label='Игнорировать:'>
					<option value='1' <?if($ignore_post_type == 1){ echo $sel; }?>>Игнорировать посты без текста(только для медиа)</option>
					<option value='2' <?if($ignore_post_type == 2){ echo $sel; }?>>Игнорировать все посты с текстом</option>
					<option value='3' <?if($ignore_post_type == 3){ echo $sel; }?>>Игнорировать посты с текстом(без медиа)</option>
					<option value='4' <?if($ignore_post_type == 4){ echo $sel; }?>>Игнорировать посты с текстом(только для медиа)</option>
					<option value='5' <?if($ignore_post_type == 5){ echo $sel; }?>>Игнорировать все посты без текста</option>
					<option value='9' <?if($ignore_post_type == 9){ echo $sel; }?>>Игнорировать все посты с видео</option>
				</optgroup>
				
				<optgroup label='Публиковать только:'>
					<option value='6' <?if($ignore_post_type == 6){ echo $sel; }?>>Посты только с текстом(без медиа)</option>
					<option value='7' <?if($ignore_post_type == 7){ echo $sel; }?>>Посты только с видео\файлы</option>
					<option value='8' <?if($ignore_post_type == 8){ echo $sel; }?>>Посты только с фото</option>
					<option value='10' <?if($ignore_post_type == 10){ echo $sel; }?>>Удалять текст из медиа постов(фото\видео\файлы)</option>
				</optgroup>
			</select>
			
			<div class='replace_link_box' <?if($filter_message <> 5){ echo 'style="display:none;"';} ?>>
			<?
				if(($filter_message == 5) && ($enable_text_message == 1)){ $dis = 'disabled';  } else { $dis = ''; }
			?>
				<label><b>Заменять на ссылку:</b></label>
				<input type="text" class="form-control form-control-sm" id='replace_link' value='<?=$replace_link;?>' <?=$dis;?>>
			</div>
			<hr>
			
			<div class='chk_inline'>
			<label><b>Функции для постов:</b></label><br>
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='filter_inline' <?if($filter_inline == 1){ echo 'checked'; }?>>
				  <label for='filter_inline' class="custom-control-label"><b>Не пропускать посты с ссылками в inline в кнопках(реклама)</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input skip_text save_opt" id='skip_text' <?if($skip_text == 1){ echo 'checked'; }?>>
				  <label for='skip_text' class="custom-control-label"><b>Игнорировать посты без медиа вложений</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='reply_post' <?if($reply_post == 1){ echo 'checked'; }?>>
				  <label for='reply_post' class="custom-control-label"><b>Игнорировать цитирования(Reply)</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='send_text_no_media' <?if($send_text_no_media == 1){ echo 'checked'; }?>>
				  <label for='send_text_no_media' class="custom-control-label"><b>Публиковать только текст(удалять медиа)</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='post_no_webpage' <?if($post_no_webpage == 1){ echo 'checked'; }?>>
				  <label for='post_no_webpage' class="custom-control-label"><b>Отключить превью от ссылок</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='ignore_album' <?if($ignore_album == 1){ echo 'checked'; }?>>
				  <label for='ignore_album' class="custom-control-label"><b>Игнорировать фото-альбомы</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='ignore_audio' <?if($ignore_audio == 1){ echo 'checked'; }?>>
				  <label for='ignore_audio' class="custom-control-label"><b>Игнорировать аудио-записи</b></label>
				</div>
			</div>
			
			</div>
		
			<div class='chk_inline'>
			<label><b>Отслеживание постов:</b></label><br>
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='update_message' <?if($update_message == 1){ echo 'checked'; }?>>
				  <label for='update_message' class="custom-control-label"><b>Обновлять текст сообщения при изменении в канале источник</b></label>
				</div>
			</div>
			
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input save_opt" id='delete_message' <?if($delete_message == 1){ echo 'checked'; }?>>
				  <label for='delete_message' class="custom-control-label"><b>Удалять сообщение при удалении его в канале источник</b></label>
				</div>
			</div>
			</div>
			
			<hr>
			<div class='chk_inline'>
			<label><b>Доп функции:</b></label><br>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input enable_text_message" id='enable_text_message' <?if($enable_text_message == 1){ echo 'checked'; }?>>
				  <label for='enable_text_message' class="custom-control-label"><b>Заменять любой текст c канала источника на свой</b></label>
				</div>
			</div>
			<textarea id='my_text_message' class='form-control' <?if($enable_text_message == 0){ echo 'style="display:none;"'; }?> placeholder='It1|It2|Kill4me|12345 - Рандом слово' title='Разделитель | 1|2|3|4|5'><?=$my_text_message;?></textarea>
			
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input replace_username_stat" id='replace_username_stat' <?if($replace_username_stat == 1){ echo 'checked'; }?>>
				  <label for='replace_username_stat' class="custom-control-label"><b>Заменять любые @username в тексте на свой</b></label>
				</div>
			</div>
			<input type='text' id='replace_username' class='form-control' value='<?=$replace_username;?>' <?if($replace_username_stat == 0){ echo 'style="display:none;"'; }?>>
			
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input bot_vote" id='enable_bot_vote' <?if($enable_bot_vote == 1){ echo 'checked'; }?>>
				  <label for='enable_bot_vote' class="custom-control-label"><b>Кнопки голосования(для фото)</b></label>
				</div>
			</div>
			
			<div class="alert alert-success bot_vote_info" role="alert" <?if($enable_bot_vote == 0){ echo 'style="display:none;"'; }?>>
			<?
				$bot_username = get_setting_account('bot_username',$self_account_id);
				if(empty($bot_username)){ $bot_username = '<span style="color:red">[Укажите token бота!]</span>'; } 
																		else { $bot_username = '@'.$bot_username; }

			?>
			 Добавте бота <b id='bot_username'><?=$bot_username;?></b> в каналы на которых будет публиковаться голосование(👍|👎).
			</div>
			
			
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input timetable" id='timetable' <?if($timetable == 1){ echo 'checked'; }?>>
				  <label for='timetable' class="custom-control-label"><b>По расписанию</b></label>
				</div>
			</div>
			
			<div id='time_box' style='display:<?=$timetable_box;?>'>
			<label><b>График публикации постов[<?=date('H:i');?>]:</b></label>
			<div class="row" style='margin-left:0;'>

				<?
				$time_post = explode('|',$data_x['time_post']);
				foreach($time_post as $val)
				{
					$checked_[$val] = true;
				}
				
				for($q=0;$q<25;$q++)
				{
					if($q < 10){ $q = "0".$q; }	
					if($checked_[$q]){ $sel = 'checked'; } else { $sel = ''; }
					
				?>
				
					<div class="custom-control custom-checkbox col-sm-2">
					  <input type="checkbox" class="custom-control-input time_post hour<?=$q;?>" id='chk<?=$q;?>' value='<?=$q;?>' <?=$sel;?>>
					  <label for='chk<?=$q;?>' class="custom-control-label"><?=$q;?>:00</label>
					</div>
				<?}?>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input limit_status" id='limit_status' <?if($limit_status == 1){ echo 'checked'; }?>>
				  <label for='limit_status' class="custom-control-label"><b>Лимит постов за указанный период</b></label>
				</div>
			</div>
			
			<div id='limit_box' style='display:<?=$limit_box;?>'>
				<div class='col-md-4'>
					<label>Максимум постов:</label>
					<input type='number' class='form-control limit_count_post' value='<?=$data_x['limit_post'];?>' min='1' style='width:102px;'>
				</div>	
				
				<div class='col-md-4'>
					<label>За часов:</label>
					<input type='number' class='form-control limit_hours' value='<?=$data_x['limit_hours'];?>' min='1' style='width:102px;'>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input forward_message save_opt" id='forward_message' <?if($forward_message == 1){ echo 'checked'; }?> <?if($enable_bot_vote == 1){ echo 'disabled'; }?>>
				  <label for='forward_message' class="custom-control-label"><b>ForwardMessage</b></label>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input length_message_limit" id='length_message_limit' <?if($length_message_limit == 1){ echo 'checked'; }?>>
				  <label for='length_message_limit' class="custom-control-label"><b>Фильтровать по кол-ву символов в сообщении</b></label>
				</div>
			</div>
			
			<div id='length_message_limit_box' style='display:<?=$length_message_limit_x;?>'>
				<div class='col-md-3'>
					<label>Кол-во символов:</label>
					<input type='number' class='form-control length_message' value='<?=$data_x['length_message']?:0;?>' min='20' style='width:102px;'>
				</div>	
				
				<div class='col-md-5'>
					<label>Пропускать, если:</label><br>
					<label title='Символов больше'> 
						<input type='radio' class='type_check_m' name='type_check_m' value='1' <?if($type_check_m == 1){ echo 'checked'; }?>> Больше
					</label><br>
					
					<label title='Символов меньше'> 
						<input type='radio' class='type_check_m' name='type_check_m' value='2' <?if($type_check_m == 2){ echo 'checked'; }?>> Меньше
					</label>
				</div>
			</div>
			
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input schedule" id='schedule' <?if($schedule == 1){ echo 'checked'; }?> <?if($enable_bot_vote == 1){ echo 'disabled'; }?>>
				  <label for='schedule' class="custom-control-label"><b>Отложенная публикация(отложенный пост)</b></label>
				  <div id='schedule_box' style='display:<?if($schedule == 0){?>none;<?}?>'>
					  <label>Публикация через N-часов. 1 - 1 час. 2-3-4-5 - рандом выборка:</label><br>
					  <input type='text' class='form-control schedule_date' id='schedule_date' name='schedule_date' value='<?=$schedule_date;?>' placeholder='Напр 1 или 2-4-6'>
					</div>
				</div>
			</div>
			
			<div class="form-check-inline">
				<div class="custom-control custom-checkbox">
				  <input type="checkbox" class="custom-control-input duplicat_dis save_opt" id='duplicat_dis' <?if($duplicat_dis == 1){ echo 'checked'; }?>>
				  <label for='duplicat_dis' class="custom-control-label"><b>Отключить проверку на дубли</b></label>
				</div>
			</div>
			
			</div>
			<hr>
			
			<button class='btn btn-primary save_setting_channel' style='width:100%;' type='1'><i class="fa fa-floppy-o" ></i> Сохранить для текущего</button><p>
			
			<button class='btn btn-primary save_setting_channel_all' style='width:100%' type='2'><i class="fa fa-floppy-o" ></i> Применить для всех</button>
			 </div>
			 
		</div>	 
	</div>	
	
	
	<div id="dump_opt" class="container tab-pane fade" >
		<div class='col-md-12 clear_p'>	
		
		<div class="col-md-5 clear_p" >
			<h5><b>Экспорт данных:</b></h5>
		
			
			<label><b>Рез.копия из Аккаунта:</b></label>
			<select class='select2' id='export_account' style='width:100%;margin-bottom:6px;'>
			<?
				$r = mysqli_query($db,"SELECT `id`,`profile_tg` FROM `accounts_setting`");
				while($list = mysqli_fetch_assoc($r))
				{
					if($list['id'] == $self_account_id){ $sel = 'selected'; } else { $sel = ''; }
					?><option value='<?=$list['id'];?>' <?=$sel;?>><?=$list['profile_tg'];?></option><?						
			?>
			
				<?}?>
			</select><hr>
			
			<label><b><i class="fa fa-question-circle" ></i> Входят данные:</b></label><br>
			<label><b>🟡 Список каналов</b></label><br>
			<label><b>🟡 Настройки фильтров</b></label><br>
			<label><b>🟡 Общие настройки</b></label><br>
			<label><b>🟡 Выпадающие списки</b></label><br>
			
			
			<button type="button" class="btn btn-danger btn-xs export_run"><i class="fa fa-save" ></i> Создать</button>
			<img src='<?=root_dir;?>/img/load.gif' id='load_export' class='load_ex' style='display:none'>
		</div>
		
		 <div class='col-md-6 clear_p' style='display:inline-table;'>
		 <h5><b>Импорт данных:</b></h5>
		 
		 <label><b>В Аккаунт:</b></label>
		<select class='select2' id='import_account' style='width:100%;margin-bottom:6px;'>
		<?
			$r = mysqli_query($db,"SELECT `id`,`profile_tg` FROM `accounts_setting`");
			while($list = mysqli_fetch_assoc($r))
			{
				if($list['id'] == $self_account_id){ $sel = 'selected'; } else { $sel = ''; }
				?><option value='<?=$list['id'];?>' <?=$sel;?>><?=$list['profile_tg'];?></option><?						
		?>
			<?}?>
		</select><hr>
		
			<div class='col-md-6 clear_p'>
				<label for="exampleFormControlFile1"><b><i class="fa fa-file"></i> Файл рез.копии:</b></label>
				<form id='upload_import_file'>
					<input type='file' name='file' class='file_import'>
					<img src='<?=root_dir;?>/img/load.gif' id='load_import' class='load_ex' style='display:none'>
				</form>
			</div>	
		
		<?
			$r = mysqli_query($db,"SELECT `id`,`channel_url`,`name` FROM `join_exists` WHERE `id_account` = '$self_account_id' AND `ready` = '1'");
			$all_join = mysqli_num_rows($r);
		?>
		
		<?if($all_join > 0){?>
		<br><label><b><i class="fa fa-exclamation" ></i> Для работы <u>необходимо вступить</u> в следующие каналы: <span id='all_join'><?=$all_join;?></span></b></label>
		<table class="table table-bordered table-striped table_x1">
		  <thead>
			<tr>
			  <th scope="col" class='opt_col' >Канал</th>
			  <th scope="col" class='opt_col invisible_'>Дейсвие</th>
			  <th scope="col" class='opt_col' style='width:41px;'>#</th>
			</tr>
		  </thead>
		  <tbody id='channels_join'>
			<?
				while($list = mysqli_fetch_array($r))
				{
					$u = "<a href='".$list['channel_url']."' target='_blank'>".$list['name']."</a>";
					?>
					<tr class='join_wait<?=$list['id'];?>'>
						<td><?=$u;?></td>
						<td>
							<button type="button" class="btn btn-danger btn-xs join_channel" value='<?=$list['id'];?>'><i class="fa fa-plus-square"></i> Вступить</button>
							<button type="button" class="btn btn-success btn-xs join_exist" value='<?=$list['id'];?>'> Уже там</button>
						</td>
						<td class='center'><i class="fa fa-trash del_channel_join" value='<?=$list['id'];?>'></i></td>
					</tr>
					<?
				}
			?>
		  </tbody>
		  </table>
		<?}?>	
		 </div>
			 
	</div>
	
	</div>
	
			
  </div>
  
   </div>
	</div>
	

	
<div class="modal fade" id="save_opt_box" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Сохранить изменения</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-group">
		  
		  <div class='col-md-12 clear_p' >
			 <label><b>Название функции:</b></label>
             <input type="text" id='el_name' class="form-control" readonly>
			 <input type='hidden' id='col_name' value=''>
			 <input type='hidden' id='col_type' value='1'>
			<hr>
			<label><i class="fa fa-bars" aria-hidden="true"></i> <b>Каналы для которых сохранить изменения:</b></label><br>			
			<select id='channel_setting_multi' class='select2 channel_setting_multi' style='width:100%;' multiple>
			<?
				$r = mysqli_query($db,"SELECT `setting_id`,`origin_name`,`publish_name` FROM `channels_origin` 
																	WHERE `account_id` = '$self_account_id'");
				while($list = mysqli_fetch_assoc($r))
				{
			?>
				<option value='<?=$list['setting_id']?>'>Источник: <?=$list['origin_name']. " | Канал публикации: ".$list['publish_name'];?></option>
				<?}?>
			</select>
		</div>
          </div>
      </div>
      <div class="modal-footer">
		<button class="btn btn-primary multi_save_opt" style="width:100%;"><i class="fa fa-floppy-o"></i> Применить</button>
      </div>
    </div>
  </div>
</div>	
	
</body>

</div>
</html>