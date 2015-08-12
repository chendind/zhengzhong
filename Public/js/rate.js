var values=[1,1];
$(document).ready(function(){
	$(".rankstars").each(function(index,element){//index starts from 0;
		$(element).click(function(e){
			//console.log($(e.target).index());
			var $target=$(e.target),i;
			if( ($target.parent(".rankstars").length>0) && ($target.hasClass('stars')) ){
				if(values[index]>$target.index()){
					for(i=$target.index();i<values[index];i++){
						$(".rankstars:eq("+index+")>img")[i].src=imgurl+'star.png';
					}
					values[index]=$target.index();
					return ;
				}
				if(values[index]<$target.index()){
					for(i=values[index];i<$target.index();i++){
						$(".rankstars:eq("+index+")>img")[i].src=imgurl+'star_yellow.png';
					}
					values[index]=$target.index();
					return ;
				}
			}
		});
	});

	$(".foot_button").click(function(){
		var $inputchked=$("input[name=isolved]:checked")
		if($inputchked.length>0){
			console.log($inputchked[0].value+' '+values.toString());
			//send msg to end-front.
		}
		else{
			console.log('是否解决了你的问题?');
		}
	});
});