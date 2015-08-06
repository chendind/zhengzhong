$(document).ready(function(){
	$(".foot_back").click(function(){
		location.href="page1.html";
	});

	$(".consult").click(function(){
		location.href="page2.html";
	});

	var oid='1000017';//获取oid
	sendMsg(oid);
});

function sendMsg(myoid){
	console.log(window.sessionStorage);
	var taboos=[];
	taboos.concat(JSON.parse(window.sessionStorage.selected01),JSON.parse(window.sessionStorage.selected02));
	taboos=taboos.toString();
	var notfounds;
	if(window.sessionStorage.notfoundArr){
		notfounds=notfoundArr;
	}
	else{
		notfounds='';
	}
	var inspections=window.sessionStorage.inspectArr;
	var diagnosis=window.sessionStorage.diagnosis;
	var age=window.sessionStorage.age;
	$.ajax({
		type: "post",
	    url: "analyze",
	    dataType: "json",
	    data:{oid: myoid,relationship: window.sessionStorage.relationship,taboo: taboos,
	    	inspect: inspections,notfound: notfounds,zhenduan: diagnosis,age: age	
	    },
	    success: function(ret){
	    	console.log(ret);
	    },
	    error: function(){
	    	console.log('network error');
	    }
	});
}