/* page2.js  诊中查询第二步的用户输入，交互的功能实现 */

var timeId, ret;
var inspectArr=new Array(),medicineArr=new Array(),notfoundArr=new Array();
var url=["getcommonname","getMedicineAndInspectList"];
$(document).ready(function(){
  $("input").each(function(index,element){
    $(element).keyup(function(){
      clearTimeout(timeId);
      $(".datalists:eq("+index+")").children().remove();
      timeId=setTimeout(timeOut(index),500);
    });
  });

  /*从datalists中选中了某个行*/
  $(".datalists").each(function(index,element){
    $(element).click(function(e){
      if(index==1){
        var types=e.target.getAttribute("ctype");
        var imgsrc='/tp/Public/img/'+types+'.png';
        var textContent=e.target.textContent;
        var inst="<div><img src="+imgsrc+" alt='pic'><span>"+textContent+"</span>";
        inst+="<img src='/tp/Public/img/deletpic.png' alt='delete' class='deleteProject'></div>";
        $(inst).prependTo($('.tableAdjust'));
        if(types=='inspect'){
          inspectArr.push(textContent);
          window.sessionStorage.setItem("inspectArr",inspectArr);
        }
        if(types=='medicine'){
          medicineArr.push(textContent);
          window.sessionStorage.setItem("medicineArr",medicineArr);
        }
        $("input:eq("+index+")")[0].value=null;
        $("input:eq(1)").focus();
      }
      else{
        $("input:eq("+index+")")[0].value=e.target.textContent;
      }
      $(element).hide();
    });
  });

  $(document).click(function(event){
    if($(event.target).parents(".datalists").size()==0){
      $(".datalists").hide();
    }
  });

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
              imgsrc='/tp/Public/img/unknow.png';
            }
            else{
             if(data.list[0].type=='medicine'){
              imgsrc='/tp/Public/img/medicine.png';
              medicineArr.push($("input:eq(1)")[0].value);
             }
             else if(data.list[0].type=='inspect'){
              imgsrc='/tp/Public/img/inspect.png';
              inspectArr.push($("input:eq(1)")[0].value);
             }
            }
            var inst="<div><img src="+imgsrc+" alt='pic'><span>"+$("input:eq(1)")[0].value+"</span>";
            inst+="<img src='/tp/Public/img/deletpic.png' alt='delete' class='deleteProject'></div>";
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
    sessions.setItem("diagnosis",$(".web_input")[0].value);
    if(sessions.getItem("oid")==null){
      console.log('no oid');
    }
    else if(!sessions.getItem("age")){
      console.log('session out of time');
      window.location.href="page1.html";
    }
    else{
      console.log('ready to post order');
    }
  });
});

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
            ret=data;
            var options,i;
            for(i=0;i<data.count;i++){
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
            }
          }
          else{
              //console.log('no ret');
              ;
          }
       }, 
      error:function (){ 
          console.log('error');
      }
  });
}

function dropElemFromArr(arr,textContent){
  if(arr.length){
    console.log(arr.length);
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
