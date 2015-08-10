/* page2.js  诊中查询第二步的用户输入，交互的功能实现 */

var timeId, ret;
var inspectArr=new Array(),medicineArr=new Array(),notfoundArr=new Array();
var url=["getcommonname","getMedicineAndInspectList"];
$(document).ready(function(){
  $("input").each(function(index,element){
    $(element).keyup(function(){
      /*更改确认或者取消*/
      if((index==1)&&($("input")[1].value)){
        $("#cancelorconfirm").html("添加");
      }
      else{
        $("#cancelorconfirm").html("取消");
      }
      clearTimeout(timeId);
      $(".datalists:eq("+index+")").children().remove();
      timeId=setTimeout(timeOut(index),500);
    });
  });

  $("#cancelorconfirm").click(function(){
    if($("#cancelorconfirm").html()=="取消"){
      $(".hidedom").hide();
    }
    else{
      //用户手动输入项的输入
      var itemName=$("input")[1].value;
      var datalists=$("div.datalists:eq(1)>div:contains("+itemName+")");//第二个候选框
      if(datalists.length==0){
        //这个项目并没有相近的候选项，类别为notfound
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
          $("input")[1].value=null;
          $("input:eq(1)").focus();
        }
      }
    }
  })

  /*输入项目框 enter 键事件*/
  $(".addProject").keypress(function(e){
    if(e.keyCode==13){
      $("#addPic").click();
    }
    //alert(e.keyCode);
  });

  /*从datalists中选中了某个行,datalists是后台获得的备选列表*/
  $(".datalists").each(function(index,element){
    $(element).click(function(e){
      if(index==1){//药品名和检查项目的备选列表
        //选中高亮所选的框
        // $(e.target).toggleClass("selectedItem");
        var itemName=e.target.textContent;
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
          //console.log('delete selected option');
        }
        $("input:eq("+index+")")[0].value=null;
        $("input:eq(1)").focus();
      }
      else{
        $("input:eq("+index+")")[0].value=e.target.textContent;
        $(element).hide();
      }
      
    });
  });

  // $(document).click(function(event){
  //   if($(event.target).parents(".datalists").size()==0){
  //     $(".datalists").hide();
  //   }
  // });

  /* 点击条目上的删除图标的行为 */
  $(document).on("click",".deleteProject",function(e){
    this.parentNode.remove();
    var contt=this.parentNode.textContent;
    var imgsrc=$(e.target).siblings("img")[0].src;
    if(imgsrc.match('medicine')){
      //console.log('1');
      if(dropElemFromArr(medicineArr,contt)==1){
        window.sessionStorage.setItem("medicineArr",medicineArr);
      }
    }
    else if(imgsrc.match('inspect')){
      if(dropElemFromArr(inspectArr,contt)==1){
        window.sessionStorage.setItem("inspectArr",inspectArr);
      }
    }
    else{
      if(dropElemFromArr(notfoundArr,contt)==1){
        window.sessionStorage.setItem("notfoundArr",notfoundArr);
      }
    }
  });
  /*
  $("#addPic").click(function(){
     var imgsrc;
     var inputVal=$("input:eq(1)")[0].value;
     if(!inputVal){
      $("input:eq(1)").focus();
      return ;
     }
     if(inputVal){
        $.ajax({
          type: "post",
          url: "getMedicineAndInspectList",
          dataType: "json",
          data:{name: inputVal},
          success: function(data){
            if(data.count==0){
              notfoundArr.push($("input:eq(1)")[0].value);
              imgsrc=curPUBLIC+'/img/unknow.png';
            }
            else{
             if(data.list[0].type=='medicine'){
              imgsrc=curPUBLIC+'/img/medicine.png';
              medicineArr.push($("input:eq(1)")[0].value);
             }
             else if(data.list[0].type=='inspect'){
              imgsrc=curPUBLIC+'/img/inspect.png';
              inspectArr.push($("input:eq(1)")[0].value);
             }
            }
            var inst="<div><img src="+imgsrc+" alt='pic'><span>"+$("input:eq(1)")[0].value+"</span>";
            inst+="<img src='"+curPUBLIC+"/img/deletpic.png' alt='delete' class='deleteProject'></div>";
            $(inst).prependTo($('.tableAdjust'));
            
            $("input:eq(1)").focus();$("input:eq(1)")[0].value=null;
          },
          error: function(){
            console.log('post error');
            retcode= 'net work error';
            $("input:eq(1)").focus();
          },
        });
     }     
  });*/

  $("#addPic").parent().click(function(){
    console.log('show hide div board.');
    $("div.hidedom").show();
    $("input")[1].focus();
  });

  $(".title_img_left").click(function(){
    window.location.href="page1.html";
  });

  $(".foot_button").click(function(){
    if(!$(".web_input")[0].value){
      console.log('no diagnosis name');
      return false;
    }
    var sessions=window.sessionStorage;
    sessions.setItem("diagnosis",$(".web_input")[0].value);//检查诊断名
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
    var inputVal=$("input:eq("+index+")")[0].value;
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
              $(options).appendTo($(".datalists:eq("+index+")"));
              $(".datalists:eq("+index+")").show();
              $("p.hidedom").hide();
            }
          }
          else{
              //console.log('no ret');
              if(index==1){
                $("p.hidedom").show();
              }
          }
       }, 
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
  $("div.msgalerted").fadeIn("normal",function(){
    $("div.msgalerted").fadeOut("normal");
  });
}

/* 检查目标项是否在已选列表中 */
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