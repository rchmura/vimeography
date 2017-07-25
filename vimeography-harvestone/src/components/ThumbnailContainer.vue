<template>
	<div class="vimeography-thumbnails">
      <thumbnail
        v-for="(video, index) in videos"
        v-bind:video="video"
        v-bind:index="index"
        v-bind:key="video.id">
      </thumbnail>
	</div>
</template>

<script>
	import Flickity from 'flickity';
	require('flickity-imagesloaded');

	import Thumbnail from './Thumbnail.vue';
	require('../../node_modules/flickity/dist/flickity.min.css');

	const ThumbnailContainer = {
		props: ['videos'],
		components: {
			Thumbnail
		},
		mounted: function() {
			this.flickity = new Flickity(this.$el, {
				initialIndex: 0,
				cellAlign: 'left',
				contain: true,
				imagesLoaded: true,
				groupCells: true,
				percentPosition: false,
				pageDots: false
			});

			this.flickity.select( 2 );

			this.flickity.on( 'select', () => console.dir(this.flickity) );
			this.flickity.on( 'scroll', progress => {
				if (progress > 0.7) {
					console.log('fetch next page');
				}
			});
		}
	}

	export default ThumbnailContainer;
</script>

<style>
	.flickity-prev-next-button {
		background: transparent;
		border-radius: 0;
		width: 28px;
		height: 28px;
	}

	.flickity-prev-next-button:hover {
		background: transparent;
	}

	.flickity-prev-next-button.next {
		right: -30px;
	}

	.flickity-prev-next-button.previous {
		left: -30px;
	}
</style>