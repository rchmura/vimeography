<template>
	<div class="vimeography-thumbnail-container">
		<div class="swiper-container">
			<div class="swiper-wrapper">
		      <thumbnail
		        v-for="(video, index) in videos"
		        v-bind:video="video"
		        v-bind:index="index"
		        v-bind:key="video.id">
		      </thumbnail>
			</div>
			<!-- If we need pagination -->
			<div class="swiper-pagination"></div>
		</div>
		<!-- If we need navigation buttons -->
		<div class="swiper-button-prev"></div>
		<div class="swiper-button-next"></div>
	</div>
</template>

<script>
	import Swiper from 'swiper';
	import Thumbnail from './Thumbnail.vue';
	require('../../node_modules/swiper/dist/css/swiper.min.css');

	const ThumbnailContainer = {
		props: ['videos'],
		components: {
			Thumbnail
		},
		mounted: function() {
			console.dir(this.$el);

			this.swiper = new Swiper(this.$el.childNodes[0], {
				initialSlide: 0,
				slidesPerView: 'auto',
				spaceBetween: 30,
				slideToClickedSlide: true,

				// If we need pagination
				pagination: '.swiper-pagination',

				// Navigation arrows
				nextButton: '.swiper-button-next',
				prevButton: '.swiper-button-prev',
				breakpoints: {
					320: {
				      slidesPerGroup: 1,
				      spaceBetween: 10
				    },
				    480: {
				      slidesPerGroup: 2,
				      spaceBetween: 20
				    },
				    640: {
				      slidesPerGroup: 3,
				      spaceBetween: 30
				    }
				}
			});
		}
	}

	export default ThumbnailContainer;
</script>

<style scoped>
.vimeography-thumbnail-container {
	position: relative;
}

.swiper-slide {
    flex-shrink: 0;
    height: 100%;
    width: auto;
    position: relative;
}

.swiper-button-prev,
.swiper-button-next {
   position: absolute;
   top: 50%;
   width: 44px;
   height: 44px;
   border: none;
   border-radius: 50%;
   background: white;
   background: hsla(0, 0%, 100%, 0.75);
   cursor: pointer;
   /* vertically center */
   -webkit-transform: translateY(-50%);
           transform: translateY(-50%);
 }
</style>
