(function($){
	$(document).ready(function(){
    	$('.theme-info').mouseover(function(){
    		$(this).stop().animate({opacity: 1}, 150);
    	}).mouseout(function(){
    		$(this).stop().animate({opacity: 0}, 150);
    	});
    	    	
    	$('.theme-container a').not('.selected a').click(function(e){
    		e.preventDefault();
    		$('#selected-vimeography-theme').val($(this).attr('data-theme'));
    		$('#vimeography-appearance-form').submit();
    	});
    	
    	$('.alert').alert();
    	       	
	});
})(jQuery)