/* page2.js  诊中查询第二步的用户输入，交互的功能实现 */

var timeId, ret;
var inspectArr=new Array(),medicineArr=new Array(),notfoundArr=new Array();
var url=["getcommonname","getMedicineAndInspectList"];
$(document).ready(function(){
$("input:eq(0)").click(function(){
            $(".new_input_board").addClass("from_input1");
            $("#addPic").click();
});
$("input:eq(1)").keyup(function(e){
        if(e.target.value){
                $("#cancelorconfirm").html("添加");
        }
        else{
                $("#cancelorconfirm").html("取消");
        }
        clearTimeout(timeId);
        $(".datalist").children().remove();
        if($(".new_input_board").hasClass("from_input1")){
                timeId=setTimeout(timeOut(0),500);
        }
        else{
                timeId=setTimeout(timeOut(1),500);
        }
});

$("#cancelorconfirm").click(function(){
        if($("#cancelorconfirm").html()=="取消"){
                $(".hidedom").hide();
                $(".new_input_board").removeClass("from_input1");
        }
        else{
      //用户手动输入项的输入
      var itemName=$("input")[1].value;
      var datalist=$("div.datalist>div:contains("+itemName+")");//候选框
      if(datalist.length==0){
        //这个项目并没有相近的候选项
        if($("input:eq(1)").hasClass("from_input1")){
        	$("input")[0].value=$("input")[1].value;
        	$("input")[1].value=null;
        	$("#cancelorconfirm").text("取消");
        	$("hidedom").hide();
        	return;
        }
        var alreadyInputs=$("div.tableAdjust>div:contains("+itemName+")");
        if(alreadyInputs.length==0){//已添加的项目中也没有这条记录
        	notfoundArr.push($("input:eq(1)")[0].value);
        	window.sessionStorage.setItem('notfoundArr',notfoundArr);
        	var inst="<div><img src='"+curPUBLIC+"/img/unknow.png' alt='pic'><span>"+$("input:eq(1)")[0].value+"</span>";
        	inst+="<img src='"+curPUBLIC+"/img/deletpic.png' alt='delete' class='deleteProject'></div>";
        	$(inst).prependTo($('div.tableAdjust'));
        	showmsg('添加成功');
        }
        else{
        	showmsg('已经添加成功了');
        }
        $("input")[1].value=null;
        $("input:eq(1)").focus();
        $("#cancelorconfirm").text("取消");
  }
}
})

/*输入项目框 enter 键事件*/
$("input:eq(1)").keypress(function(e){
        if(e.keyCode==13){
                $("#cancelorconfirm").click();
        }
});

/*从datalist中选中了某个行,datalist是后台获得的备选列表*/
$(".datalist").click(function(){
        var itemName=e.target.textContent;
        if($(".new_input_board").hasClass("from_input1")){
                $("input:eq(0)").value=itemName;
                $("#cancelorconfirm").text("取消");
                $(".new_input_board").removeClass("from_input1");
                $("hidedom").hide();
                return;
        }
        var types=e.target.getAttribute("ctype");
        var msg="添加成功";
         if(!chkItemExist(itemName,types)){//添加medicine or inspect
                var imgsrc=curPUBLIC+'/img/'+types+'.png';
                var textContent=e.target.textContent;
                var inst="<div><img src="+imgsrc+" alt='pic'><span>"+textContent+"</span>";
                inst+="<img src='"+curPUBLIC+"/img/deletpic.png' alt='delete' class='deleteProject'></div>";
                $(inst).prependTo($('.tableAdjust'));//添加到对应的药物、检查项目列表里；
                if(types=='inspect'){
                        inspectArr.push(textContent);
                        window.sessionStorage.setItem("inspectArr",inspectArr);
                }
                if(types=='medicine'){
                        medicineArr.push(textContent);
                        window.sessionStorage.setItem("medicineArr",medicineArr);
                }
                showmsg(msg);//淡入淡出消息框
         }
         else{
                msg="取消成功";
                showmsg(msg);
         }
});
 
/* 点击条目上的删除图标的行为 */
$(document).on("click",".deleteProject",function(e){
        this.parentNode.remove();
        var contt=this.parentNode.textContent;
        var imgsrc=$(e.target).siblings("img")[0].src;
        if(imgsrc.match('medicine')){
                if(dropElemFromArr(medicineArr,contt)==1){
                        window.sessionStorage.setItem("medicineArr",medicineArr);
                        return;
                }
        }
        if(imgsrc.match('inspect')){
                if(dropElemFromArr(inspectArr,contt)==1){
                        window.sessionStorage.setItem("inspectArr",inspectArr);
                        return;
                }
        }
        else{
                if(dropElemFromArr(notfoundArr,contt)==1){
                        window.sessionStorage.setItem("notfoundArr",notfoundArr);
                        return;
                }
        }
});

$("#addPic").parent().click(function(){
	console.log('show hide div board.');
	$("div.hidedom").show();
	$("input")[1].focus();
});

$(".title_img_left").click(function(){window.location.href="page1.html";});

$(".foot_button").click(function(){
	if(!$("input")[0].value){
		console.log('no diagnosis name');
		return false;
	}
	var sessions=window.sessionStorage;
    sessions.setItem("diagnosis",$("input")[0].value);//检查诊断名
    // if(sessions.getItem("oid")==null){
    //   console.log('no oid');
    // }
    // else
    if(!sessions.getItem("age")){  //检查上一页的表单信息
    	console.log('session out of time');
    	window.location.href="page1.html";
    }
    else{
      // console.log('ready to post order');
      location.href="page3.html";
}
});
});/* end of document.ready */

/* timeOut 延迟发送post查找匹配的诊断名，药物，项目名*/
function timeOut(index){
        var inputVal=$("input:eq(1)")[0].value;
        $.ajax({ 
               type: "post", 
               url: url[index], 
               dataType: "json",
               data:{name:inputVal},
               success: function (data) {
                      if (data.count>0) {
                             console.log(data);
                             ret=data;
                             var options,i,j=Math.min(data.count,50);
                             for(i=0;i<j;i++){
                                    if(index==0){
                                           options="<div>"+data.list[i].name+"</div>";
                                   }
                                   else{
                                           if(data.list[i].type=="medicine"){
                                                  options="<div ctype='medicine'>"+data.list[i].medicine_productname+"</div>";
                                          }
                                          else{
                                                  if(data.list[i].type=="inspect"){
                                                         options="<div ctype='inspect'>"+data.list[i].inspect_name+"</div>";
                                                 }
                                         }
                                 }
                                 $(options).appendTo($(".datalist"));
                                 $(".datalist").show();
                                 $("p.hidedom").hide();
                         }
                 }
                 else{
                      //console.log('no ret');
                      $("p.hidedom").show();
                      $(".datalist").hide();
              }
        }, //end of success
        error:function (){ 
        	console.log('error');
        }
});
}

function dropElemFromArr(arr,textContent){
	if(arr.length){
    //console.log(arr.length);
    var length=arr.length,i;
    for(i=1;i<=length;i++){
    	if(arr[i-1]==textContent){
    		arr.splice(i-1,1);
    		return 1;
    	}
    }
}  
return 0;
}

/* 淡入淡出控制函数 */
function showmsg(msg){
        console.log(msg);
        $("div.msgalerted").text(msg);
        $("div.msgalerted").fadeIn("normal",function(){$("div.msgalerted").fadeOut("normal")});
}

/* 检查目标项是否在已选列表中 
true:曾经有，现已删除
false:没有
*/
function chkItemExist(itemName,type){
        var tableAdjustDivs=$(".tableAdjust>div");
        if(tableAdjustDivs.length==0){
               return false;
       }
       var i,length=tableAdjustDivs.length,flaggg=false;
       for(i=0;i<length;i++){
               if(tableAdjustDivs[i].textContent==itemName){
                      $(tableAdjustDivs[i]).remove();
                      if(type=='medicine'){
                             if(dropElemFromArr(medicineArr,itemName)==1){
                                    window.sessionStorage.setItem("medicineArr",medicineArr);
                            }
                    }
                    if(type='inspect'){
                             if(dropElemFromArr(inspectArr,itemName)==1){
                                    window.sessionStorage.setItem("inspectArr",inspectArr);
                            }
                    }
                    return true;
            }
        }
        return false;
}