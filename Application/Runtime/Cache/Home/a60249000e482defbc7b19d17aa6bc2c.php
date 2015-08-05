<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="user-scalable=no">
	<meta name="viewport" content="initial-scale=1,maximum-scale=1">
	<meta name="viewport" content="width=device-width">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title>诊中1</title>
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/common.css">
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/specific.css">
	<link rel="stylesheet" type="text/css" href="/tp/Public/CSS/generics.css">
	<script src="/tp/Public/js/jquery-2.1.4.min.js"></script>
	<script src="/tp/Public/js/page1.js"></script>
</head>
<body>
	<div class="title hightlight"><span>诊中查询</span></div>

	<div class="content">
		<div class="square_select">
			<p class="title_p">查询人与您的关系:</p>
			<div class="box" id="boxsp01">我</div>
			<div class="box" id="boxsp02">我的父母</div>
			<div class="box" id="boxsp03">我的子女</div>
			<div class="box" id="boxsp04">我的配偶</div>
			<div class="clear"></div>
			<div class="box" id="boxsp05">自定义</div><input type="text" class="hidedom autoset">
		</div>
		<div class="square">
			<p class="title_p">查询人年龄:<span> </span><input class="autoset small_input" type="number"> <span>岁</span></p>
		</div>
		<div class="square_select">
			<p class="title_p">查询人所属人群:</p>
			<div class="box" id="boxsp06">婴幼儿</div>
			<div class="box" id="boxsp07">孕妇</div>
			<div class="box" id="boxsp08">普通人</div>
		</div>
		<div class="square">
			<div class="tables">
				<p>药物过敏史：</p>
				
				<select name="medAllergy" id="medAllergy" multiple="multiple" size="5">
					<option value="illness01">ill1</option>
					<option value="illness02">ill2</option>
					<option value="illness03">ill3</option>
					<option value="illness04">ill4</option>
					<option value="illness05">ill5</option>
				</select>
				<div class="datalists astable" id="datalist01"></div>
			</div>
			<div class="tables">
				<p>慢性病史：</p>
				
				<select name="chronic" id="chronic" multiple="multiple" size="5">
					<option value="chronic01">ch01</option>
					<option value="chronic02">chronic2</option>
					<option value="chronic03">chronic3</option>
					<option value="chronic04">chronic4</option>
					<option value="chronic05">chronic5</option>
				</select>
				<div class="datalists astable" id="datalist02"></div>
			</div>
		</div>
		<div class="foot_button"><button>下一步</button></div>
	</div>

	

	<div class="foot"></div>
	<script>
	/*   以下是select 框选中部分发生改变时，对应的datalist框发生对应改变的事件  */
	$("select").change(function(){
		var $selected=$(this).children('option:selected');
		var length=$selected.size();
		var i,insts;
		var datalists=$(this).next(".datalists");
		datalists.children().remove();
		for(i=0;i<length;i++){
			insts="<div><span>"+$selected[i].value+"</span><img src='/tp/Public/img/deletpic.png' alt='delete' class='deleteProject'></div>";
			$(insts).appendTo(datalists);
		}
		if(length>=1){
			datalists.show();
		}
	});
	</script>
</body>
</html>