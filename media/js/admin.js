(function($){
	$(document).ready(function(){
    	$('.theme-info').mouseover(function(){
    		$(this).stop().animate({opacity: 1}, 150);
    	}).mouseout(function(){
    		$(this).stop().animate({opacity: 0}, 150);
    	});
    	
    	change_url();
    	
    	$('#vimeography-source').change(function(){
    		change_url();
    	});
    	
    	$('.theme-container a').click(function(e){
    		e.preventDefault();
    		$('#selected-vimeography-theme').val($(this).attr('data-theme'));
    		$('#vimeography-appearance-form').submit();
    	});
    	
    	$('.alert').alert();
    	    	
    	function change_url()
    	{
	    	source = $('#vimeography-source').val();
	    	$url = $('#vimeography-source-url span');
	    	
	    	switch (source)
	    	{
	    		case 'user':
	    			$($url).html('');
	    			break;
	    		case 'channel':
	    		case 'group':
	    			$($url).html(source+'s/');
	    			break;
	    		case 'album':
	    			$($url).html(source+'/');
	    			break;
	    		default:
	    			$($url).html('');
	    			break;
	    	}
    	}
    	
	});
})(jQuery)