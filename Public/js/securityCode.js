var t=10;
$(document).ready(function(){
	//bind getSecurityCode on button#getcode;
	$("#getcode").bind('click',function(){
		t=15;
		var phoneNum=chkPhoneNum();
		if(!phoneNum){
			return;
		}
		var posturl="/index.php/Home/Login/registerCheckCode";//获取验证码
		$.ajax({
			type:"post",
			url: posturl,
			dataType: "json",
			data:{phone: phoneNum},
			success: function(ret){
				console.log(ret);
				if(ret.state=='30005'){
					alert('请输入合理的手机号码');
				}
				if(ret.state=='0'){
					console.log("稍等，验证码正在路上");
					var msg="已发送验证码至"+phoneNum.toString().substring(0,3)+"****"+phoneNum.toString().substring(6,4)+"，验证码24小时有效，请尽快使用。";
					$("<p>"+msg+"</p>").appendTo($(".webinput:eq(1)"));
				}
			},
			error: function(){
				console.log("network error.");
			}
		});
		showTime();
	});

	//send SecurityCode and phoneNum to check usr.
	
	$("#send").bind('click',function(){
		var secCode=$("#secCode")[0].value;
		if(secCode.length==0){
			alert('no secCode');
			return;
		}
		var usrphone=chkPhoneNum();
		if(!usrphone){
			return;
		}
		var posturl="/index.php/Home/Login/registerCheck";//检验验证码

		$.ajax({
			type:"post",
			url: posturl,
			dataType: "json",
			data:{phone: usrphone,code: secCode},
			success: function(data){
						console.log(data);
						if(data.state=='0'){
							console.log('success');
							alert("验证成功");//验证码和手机号吻合。
						}
						else{
							console.log('phone or SecurityCode error');
						}
					},
			error: function(){
				console.log('network error');
			}
		});
	});
	
	
});

//show counting down time.
function showTime(){  
    t -= 1;
    var showmsg="重新获取("+t+"s)";
    $("#getcode").html(showmsg);
   	$("#getcode").attr("disabled","disabled");

    if(t==0){
    	// clearTimeout(timeID);
        $("#getcode").removeAttr("disabled");
        $("#getcode").html("获取验证码");
        return ;
    }
    //每秒执行一次,showTime()  
    setTimeout("showTime()",1000);  
}

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