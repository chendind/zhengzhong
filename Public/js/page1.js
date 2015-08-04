var selected01=new Array(),selected02=new Array(),relationship,age,kind;
$(document).ready(function(){
	/**  重新加载页面时检查是否有session可以不用再填 **/
	chkSessions();

	/* *以下是绑定的事件* */
	$(document).click(function(){
		if($(".square_select,.small_input").hasClass("warning")){
			$(".square_select,.small_input").removeClass("warning");
		}
	});
	/** 以下是显示单选的两个区块的绑定函数  **/
	$(".box").click(function(){
		$(this).siblings(".hightlight").removeClass("hightlight").end().addClass('hightlight');
		if((this.id!="boxsp05")&&($(".hidedom:eq(0)").is(":visible"))&&($(this).parents(".square_select").index()<=1)) {
			$(".hidedom:eq(0)").slideUp("fast");
			//$(".hidedom")[0].value=null;
		}
	});
	/* datalist里面的删除按钮事件绑定 */
	$(document).on("click",".deleteProject",function(e){
		//console.log(this.parentNode);
		var optionVal=$(this.parentNode).text();
		var selected=$(this).parents(".datalists").prev("select").children("option:selected");
		//console.log($(this).parents(".datalists"));
		var length=selected.size();
		var i;
		for(i=0;i<length;i++){
			if(optionVal==selected[i].value){
				selected[i].selected=false;
				continue ;
			}
		}
		$(this.parentNode).remove();
	});

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

	/**  以下是关系选项的自定义部分的效果 **/
	var autoset=$("#boxsp05");
	autoset.click(function(){//自定义部分的input显示
		if($(".hidedom:eq(0)").is( ":hidden" )){
			$(".hidedom:eq(0)").slideDown("fast");	
		}
	});

	/**  以下是‘下一步’按钮绑定事件 **/
	$(".foot_button").click(function(){
		if(chekItem()){
			window.sessionStorage.setItem("relationship",relationship);
			window.sessionStorage.setItem("age",age);
			window.sessionStorage.setItem("kind",kind);
			window.sessionStorage.setItem("selected01",JSON.stringify(selected01));
			window.sessionStorage.setItem("selected02",JSON.stringify(selected02));
			window.location.href="page2.html";
			//console.log('success');
		}
		else{
			alert("好像还有项目没有填哦");
			console.log("好像还有项目没有填哦");
		}
	});
});

/** chekItem 方法检查各项目是否填写 **/
function chekItem(){
	var flag=true;
	$(".square_select").each(function(){
		if($(this).children(".box.hightlight").size()==0){
			$(this).addClass("warning");
			console.log("1");
			flag=false;
		}
	});

	if(!$(".small_input")[0].value){
		flag=false;//no age input
		$(".small_input:eq(0)").addClass("warning");
	}
	if(!flag){
		console.log("2");
		return false;
	}
	var $selectedBox1=$(".square_select:eq(0)").children(".box.hightlight");
	if($selectedBox1[0].id=="boxsp05"){
		if($("input.hidedom")[0].value){
			relationship=$(".hidedom")[0].value;
		}
		else{
			$(".square_select:eq(0)").addClass("warning");//no input
			console.log("3");
			return false;
		}
	}
	else{
		relationship=$selectedBox1.text();
	}
	$selectedBox1=$(".square_select:eq(1)").children(".box.hightlight");
	kind=$selectedBox1.text();
	age=parseInt($("input.small_input")[0].value);
	if(isNaN(age)&&age){
		$("input.small_input").val("");
		console.log("4");
		return false;
	}
	var selectM01=$("#medAllergy")[0];
	var selectM02=$("#chronic")[0];
	var i,length=selectM01.options.length;
	selected01=[];selected02=[];
	for(i=0;i<length;i++){
		if(selectM01.options[i].selected){
			selected01.push(selectM01.options[i].value);
		}
	}
	length=selectM02.options.length;
	for(i=0;i<length;i++){
		if(selectM02.options[i].selected){
			selected02.push(selectM02.options[i].value);
		}
	}
	// if(selected01.length>1){
	// 	selected01.splice(0,1);
	// }
	// if(selected02.length>1){
	// 	selected02.splice(0,1);
	// }
	//console.log(selected01);
	return true;
}

function chkSessions(){
	//console.log('chkSessions');
	var sessions=window.sessionStorage;

	if(sessions.getItem("relationship")){
		relationship=sessions.getItem("relationship");
		switch (relationship){
			case '我' : $("#boxsp01").first().addClass("hightlight");break;
			case '我的父母' : $("#boxsp02").addClass("hightlight");break;
			case '我的子女' : $("#boxsp03").addClass("hightlight");break;
			case '我的配偶' : $("#boxsp04").addClass("hightlight");break;
			default : $("#boxsp05").addClass("hightlight");
					  $(".hidedom:eq(0)").css("display","inline-block");
					  $(".hidedom")[0].value=relationship;
		}
		
	}
	if(sessions.getItem("age")){
		age=parseInt(sessions.getItem("age"))
		if(!isNaN(age)){
			$(".small_input")[0].value=age;
		}
	}
	if(sessions.getItem("kind")){
		kind=sessions.getItem("kind");
		switch(kind){
			case '婴幼儿':$($(".square_select:eq(1) div.box")[0]).addClass("hightlight");break;
			case '孕妇':$($(".square_select:eq(1) div.box")[1]).addClass("hightlight");break;
			case '普通人':$($(".square_select:eq(1) div.box")[2]).addClass("hightlight");break;
			default :;
		}
	}
	if(sessions.getItem("selected01")){
		selected01=JSON.parse(sessions.getItem("selected01"));
		if(selected01.length){
			var s01_length=selected01.length,i,j;
			var selectM01=$("#medAllergy")[0];
			
			var length=selectM01.options.length;
			for(i=0;i<length;i++){
				for(j=0;j<s01_length;j++){
					if(selectM01.options[i].value==selected01[j]){
						selectM01.options[i].selected=true;
						continue ;
					}
				}
			}
		}
	}
	if(sessions.getItem("selected02")){
		selected02=JSON.parse(sessions.getItem("selected02"));
		if(selected02.length){
			var s02_length=selected02.length;
			var selectM02=$("#chronic")[0];
			length=selectM02.options.length;
			for(i=0;i<length;i++){
				for(j=0;j<s02_length;j++){
					if(selectM02.options[i].value==selected02[j]){
						selectM02.options[i].selected=true;
						continue ;
					}
				}
			}
		}
	}
	$("select").each(function(){
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
}