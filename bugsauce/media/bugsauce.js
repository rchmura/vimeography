(function($){
	$(document).ready(function(){

		$('#vimeography-gallery-' + gallery_id).fitVids();
		
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-thumbnails').flexslider({
			animation: "slide",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			itemWidth: 186,
			itemMargin: 8,
			maxItems: 4,
			minItems: 2,
			useCSS: false
		});
		
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-thumbnails li').first().addClass('flex-active-slide');
		
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-thumbnails img').click(function(e) {
			var id = $(this).attr('data-id');
			var src = 'http://player.vimeo.com/video/'+id+'?title=0&byline=0&portrait=0&autoplay=0&api=1&player_id=vimeography-embed-'+ gallery_id;
			
			$('.flex-active-slide').removeClass('flex-active-slide');
			$(this).parent().addClass('flex-active-slide');
						
			
			$('#vimeography-embed-' + gallery_id).animate({'opacity':0}, 300, 'linear', function(){
				$(this).attr('src', src); 
				$(this).load(function(){
					$(this).animate({'opacity':1}, 300);
				});
			});
			
    		e.preventDefault();

		});
						
	});
})(jQuery)