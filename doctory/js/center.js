$(document).ready(function(e) {
	var _width = document.body.offsetWidth;
	var _height = document.body.offsetHeight;
	$(".center_box").css({"width":_width,"height":_height*0.28});	
	var _top_shift = document.body.offsetHeight - $(".ui-footer").height() - 60;
	$(".button_").css("top",_top_shift);

});