<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="user-scalable=no">
	<meta name="viewport" content="initial-scale=1,maximum-scale=1">
	<meta name="viewport" content="width=device-width">
	<title>诊中2</title>
	<link rel="stylesheet" type="text/css" href="/zhengzhong/Public/CSS/page2.css">
	<script src="/zhengzhong/Public/js/jquery-2.1.4.min.js"></script>
	<script src="/zhengzhong/Public/js/page2.js"></script>
</head>
<body>
	<!-- .astable可以设置其子元素中的div是类似于table的竖排。只有一列,每一行都是由其子元素来撑开的。 -->
	<!-- <div class="astable">
		<div>1</div>
		<div>2</div>
		<div>3</div>
		<div>4</div>
	</div> -->
	<div class="title">
		<div class="title_img_left">
			<img src="/zhengzhong/Public/img/backpic.png" alt="backpic">
		</div>
		
		<span>诊断输入</span>
		<div class="title_img_right">
			<img src="/zhengzhong/Public/img/mailpic.png" alt="backpic">
		</div>
	</div>
	
	<div class="content">
		<h1>查询人的诊断名</h1>
		<div>
			<input type="text" list="dlsearch" 
			class="web_input" placeholder="请输入医生为查询人开出的诊断名">
			<div class="datalists astable" id="datalist01"></div>
		</div>
		
		<p class="smallType">Tips: 若不知道诊断名，请前往人工咨询</p>
		<p>查询人的药物处方和检查项目：</p>
		<div class="tableAdjust">
			<div>
				<img src="/zhengzhong/Public/img/addpic.png" alt="添加" id="addPic">
				<input class="addProject specialInput" placeholder="添加项目">
			</div>
		</div>
		<div class="datalists astable" id="datalist02"></div>
	</div>
	
	<div class="foot_button"><button>确认并提交</button></div>
</body>
</html>