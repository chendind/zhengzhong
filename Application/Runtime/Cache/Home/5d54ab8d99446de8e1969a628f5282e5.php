<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>login</title>
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/common.css">
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/specific_login.css">
	<script src="/tp/Public/js/jquery-2.1.4.min.js"></script>
	<script src="/tp/Public/js/md5.js"></script>
</head>
<body>
	<div class="title">login page</div>
	<div class="content">
		<div class="webinput"><input id="phoneNum" type="text" placeholder="userphone"></div>
		<div class="webinput"><input id="password" type="password" placeholder="password"></div>
		<p><a href="#">forget your password?</a></p>
		<div class="foot_button"><button id="loginbtn">login</button></div>
	</div>

	<script type="text/javascript">
		//web check phonenumber whether it is recognized.
		function chkPhoneNum(){
			var usrphone=$("#phoneNum")[0].value;
			usrphone=parseInt(usrphone);
			if(usrphone.toString().length<11){
				console.log('unrecognized phone number.');
				return false;
			}
			else{
				return usrphone.toString();
			}
		} 

		$("#loginbtn").bind('click',function(){
			var usrphone=chkPhoneNum();
			if(!usrphone){
				return;
			}
			var usrpass=$("#password")[0].value;
			usrpass=hex_md5(usrpass);
			var purl="/tp/index.php/Home/Login/login";
			
			$.ajax({
				type:"post",
				url: purl,
				dataType: "json",
				data:{phone: usrphone,pass: usrpass},
				success: function(data){
							console.log(data);
							if(data.state=='0'){
								console.log('success');
							}
							else{
								console.log('phone or pass error');
							}
						},
				error: function(){
					console.log('network error');
				}
			});
		});//end of login.
	</script>
</body>
</html>