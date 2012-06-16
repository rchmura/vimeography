(function($){
	$(document).ready(function(){
	
		// The slider being synced must be initialized first
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-thumbnails').flexslider({
			animation: "slide",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			itemWidth: 184,
			itemMargin: 8,
			asNavFor: '#vimeography-gallery-' + gallery_id + ' .vimeography-main',
			maxItems: 4,
			minItems: 2,
			move: 4
		});
		
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-main').flexslider({
			animation: "fade",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			sync: "#vimeography-gallery-' + gallery_id + ' .vimeography-thumbnails",
			video: true
		});
		
		$('#vimeography-gallery-' + gallery_id + ' .vimeography-main').fitVids();
		
	});
})(jQuery)