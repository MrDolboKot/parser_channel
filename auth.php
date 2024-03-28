<?php
	set_time_limit(0); 
	error_reporting(0);
	ini_set('display_errors', 0);
	#require_once 'vendor/autoload.php';

	
	require_once 'vendor/autoload.php';
	use danog\MadelineProto\Stream\Proxy\SocksProxy;
	use danog\MadelineProto\Stream\MTProtoTransport\ObfuscatedStream;
	use danog\MadelineProto\Stream\Proxy\HttpProxy;
	use danog\MadelineProto\Logger;
	
	$path = dirname(__FILE__);
	
	$device_model_list = file('device_model.txt');
	$device_model = $device_model_list[array_rand($device_model_list)];
	
	$settings['authorization']['default_temp_auth_key_expires_in'] = 86400 * 30 * 12;
	$settings['app_info']['api_id'] = 2496;
	$settings['app_info']['api_hash'] = '8da85b0d5bfe62527e5b244c209159c3';
	$settings['logger']['logger'] = 0;
	$settings['app_info']['device_model'] = $device_model;
	$settings['app_info']['system_version'] = rand(1,15).'.0';
	$settings['app_info']['app_version'] ='Android '.rand(1,15).'.0';
	$settings['app_info']['lang_code'] = 'eng';
	#$settings['app_info']['lang_pack'] = '';
	$settings['ipc']['slow'] = true;
	
	$login = readline('Enter your phone number:');
	$login = str_replace(' ','',$login);
	$profile = $path.'/account/'.$login.'.tg';
	$profile = str_replace('+','',$profile);
	
	try
	{ 
		$MadelineProto = new \danog\MadelineProto\API($path.'/'.$profile,$settings);
		$MadelineProto->phone_login($login); 
		$authorization = $MadelineProto->completePhoneLogin(readline('Enter the code you received: ')); 
		if ($authorization['_'] === 'account.noPassword') {
			throw new \danog\MadelineProto\Exception('2FA is enabled but no password is set!');
		}
		if ($authorization['_'] === 'account.password') {
			$authorization = $MadelineProto->complete2falogin(readline('Please enter your password (hint '.$authorization['hint'].'): ')); 
		}
		if ($authorization['_'] === 'account.needSignup') {
			$authorization = $MadelineProto->completeSignup(readline('Please enter your first name: '), readline('Please enter your last name (can be empty): '));
		}

		unset($MadelineProto);
		echo "Profile:$profile - Created!\n";
	}catch(\danog\MadelineProto\RPCErrorException $e) {  echo $error = $e->getMessage()."\n";exit; }
	
?>