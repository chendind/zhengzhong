$(document).ready(function(e) {
	var _width = document.body.offsetWidth;
	var _height = document.body.offsetHeight;
	$(".news_box").css({"width":_width,"height":_height*0.28});	
	// var exp_box_height = _height - $(".ui-header").height() - $(".ui-footer").height() - $(".news_box").height() - $(".news_box2").height() - 5;
	// $(".exp_box").css({"height":exp_box_height});	
	// $(".exp_box li").css({"width":"100%","height":exp_box_height*0.32});
	$(".news_box img").each(function(index, element) {
        $(this).css({"width":_width,"height":_height*0.276});
    });
	
    var slider = Swipe(document.getElementById('scroll_img'), {
		auto: 3000,
		continuous: true,
		callback: function(pos) {
				var i = bullets.length;
				while (i--) {
					bullets[i].className = ' ';
				}
				bullets[pos].className = 'on';
		}
	});
	var bullets = document.getElementById('scroll_position').getElementsByTagName('li');
	$(function(){
		$('.scroll_position_bg').css({
			width:$('#scroll_position').width()
		});
	});
	//console.log($(".news_box2").width());
});