$(document).ready(function(){
	$("button").click(function(){
		$(".flip>div")[0].style.webkitTransform="rotateY(180deg)";
		$(".flip>div")[1].style.webkitTransform="rotateY(0deg)";
	});

});

$(document).ready(function(){
     $("#timings-demo-btn").toggle(
        function(){
          $(this).next("div#timings-demo").addClass("timings-demo-hover");
        },function(){
          $(this).next("div#timings-demo").removeClass("timings-demo-hover");
     });

     $('.banner').unslider();
  });