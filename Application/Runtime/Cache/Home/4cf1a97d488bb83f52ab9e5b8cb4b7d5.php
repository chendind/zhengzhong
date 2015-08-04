<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style>
		p{
			background-color: #f00;
		}
		.inl{
			background-color: #ccc;
			display: inline;
		}
		img{
			vertical-align: middle;
		}
		.tableAll>div{
			height: 80px;
			line-height: 80px;
			background-color: #ccc;
			border-bottom: 1px solid;
		}
		.tableAll{
			max-height: 300px;
			overflow-y: auto;
			overflow-x: hidden;
		}
	</style>
</head>
<body>
	<div class="tableAll">
		<img src="/tp/Public/img/medicine.png" alt="medc">
		<span>nihao</span>
		<div style="display:inline;line-height:40px;">nihao<br>hellld</div>
		<div></div>
		<div></div>
		<div></div>
	</div>
	<div>.</div>
</body>
</html>