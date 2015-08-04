<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="user-scalable=no">
	<meta name="viewport" content="initial-scale=1,maximum-scale=1">
	<meta name="viewport" content="width=device-width">
	<title>login</title>
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/common.css">
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/specific_login_securityCode.css">
	<script src="/tp/Public/js/jquery-2.1.4.min.js"></script>
	<script src="/tp/Public/js/securityCode.js"></script>
</head>
<body>
	<div class="title">asign page</div>
	<div class="content">
		<div class="webinput"><input id="phoneNum" type="text" placeholder="phone number"></div>
		<div class="webinput">
			<input id="secCode" type="text" placeholder="security code">
			<button class="msglabel" id="getcode">获取验证码</button>
		</div>
		<div class="foot_button"><button id="send">send</button></div>
	</div>
</body>
</html>