<?php
	include('functions.php');
	$login = strip_tags($_POST['login']); $login = htmlspecialchars($login,ENT_QUOTES); $login = stripslashes($login);
	$pass = strip_tags($_POST['password']); $pass = htmlspecialchars($pass,ENT_QUOTES);$pass = stripslashes($pass);
	
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{	
		if((!empty($login)) && (!empty($pass)))
		{	
			if(autorize($login,$pass,1)){  header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir.'/'); } 
					else {  header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir); }
		} else { header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir); }
	}
	
	if($session)
	{
		header('location: '.$ssl.$_SERVER['SERVER_NAME'].root_dir.'/');
	} 
	
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Авторизация</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">


  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
 
 
  <style>
	html, body{height: inherit;}
	b{color: #fff;font-size: 25px;}
	.login-box{ }
	.login-page {
		background: url(<?=root_dir?>/img/bg.jpg) no-repeat #000;
		background-size:cover;
	}
	
	.content
	{
		z-index: 9999;
		width: 100%;
		padding: 89px 0 7px;
	}
	.login-box-body, .register-box-body {
		background: #00000087;
		padding: 16px 22px;
		border-top: 0;
		color: #d0d1d2;
		-webkit-border-radius: 9px;
		-moz-border-radius: 9px;
		border-radius: 9px;
		width: 373px;
		margin: 45px auto;
	}
	.form-control{color:#464545;background-color:#c5c5c5bf;}
	.btn-primary {background-color: #ca522c;border-color: #7b7b7b;}
	.value_{padding:6px 7px;width:100%;color: #f3dc98;font-weight: 600;border:2px #6f6e6e solid; background: #1d1c1c4d;}
	.error{margin-top: 2px;display: block;background: transparent;color: #fdfdfd;}
	.btn-primary {background-color: #353332;border-color: #272020;}
  </style>
</head>
<body class="hold-transition login-page">

<div class='content'>
<div class="login-box">
  <div class="login-logo">
  
  </div>
  <div class="login-box-body">
    <p class="login-box-msg">Требуется авторизация.</p>

    <form action="<?=root_dir;?>/login.php" method="post">
      <div class="form-group has-feedback">
        <input type="login" class="form-control" placeholder="Логин" name='login' >
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" placeholder="Пароль" name='password'>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
	  <button type="submit" class="btn btn-primary btn-block btn-flat"><i class="fa fa-arrow-right" aria-hidden="true"></i> Вход</button>
     
    </form>
  </div>
</div>
</div>

</body>
<style>
	.ui-widget-content{background:#292929;}
</style>
</html>
