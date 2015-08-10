<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="user-scalable=no">
	<meta name="viewport" content="initial-scale=1,maximum-scale=1">
	<meta name="viewport" content="width=device-width">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title>诊中2</title>
	<link rel="stylesheet" type="text/css" href="/zhengzhong/Public/CSS/common.css">
	<link rel="stylesheet" type="text/css" href="/zhengzhong/Public/CSS/page2.css">
	<script src="/zhengzhong/Public/js/jquery-2.1.4.min.js"></script>
	<script src="/zhengzhong/Public/js/page2.js"></script>
</head>
<body>
	<div class="title hightlight">
		<img class="title_img_left" src="/zhengzhong/Public/img/backpic.png" alt="backpic">
		<span>诊断输入</span>
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
		<div>
			<img src="/zhengzhong/Public/img/addpic.png" alt="添加" id="addPic">
			<span class="addProject specialInput">添加项目</span>
		</div>
		<div class="tableAdjust">
			<!-- <div>
				<img src="/zhengzhong/Public/img/addpic.png" alt="添加" id="addPic">
				<input class="addProject specialInput" placeholder="添加项目">
			</div> -->
		</div>
		<!-- <div class="datalists astable" id="datalist02"></div> -->
	</div>
	<div class="hidedom new_input_board"> <!-- 弹出的输入框界面，输入药物和检查项目 -->
		<div class="new_board">
			<input type="text" placeholder="药物处方或者检查项目"><span id="cancelorconfirm">取消</span>
			<p class="smallType hidedom">请检查是否有生僻字，检查无误后按确认键输入</p>
			<div class="datalists astable" id="datalist02"></div>
		</div>
		<div class="msgalerted">添加成功</div>
	</div>
	<div class="foot_button"><button class="hightlight">确认并提交</button></div>
	<script>
		curURL='/zhengzhong/index.php/Home/Analyze';//对应controller的路径
		curPUBLIC='/zhengzhong/Public';//Public文件夹路径
	</script>
</body>
</html>