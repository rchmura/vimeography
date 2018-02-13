<script>
  import { mapState, mapActions } from 'vuex'

  import { Player, Filters } from 'vimeography-blueprint';

  import ThumbnailContainer from './ThumbnailContainer.vue';

  const template = `
    <div class="vimeography-gallery">
      <filters v-if="this.pro"></filters>
      <player :activeVideo="this.activeVideo"></player>
      <thumbnail-container :videos="videos" :activeVideoId="this.activeVideo.id"></thumbnail-container>
    </div>
  `;

  const Gallery = {
    name: 'gallery',
    template,
    methods: {
      ...mapActions([
        'loadVideo',
      ]),
    },
    watch: {
      '$route' (to, from) {
        const videoId = to.query.vimeography_video;
        const gallery = to.query.vimeography_gallery;

        if (videoId && gallery && parseInt(gallery) === this.galleryId) {
          this.loadVideo(videoId)
        }
      }
    },
    computed: {
      ...mapState({
        activeVideo: state => state.videos.items[state.videos.active],
        galleryId: state => state.gallery.id,
        pro: state => state.gallery.pro
      }),
      videos() {
        return this.$store.getters.videos
      }
    },
    components: {
      Player,
      Filters,
      ThumbnailContainer
    }
  }

  export default Gallery;
</script>

<style lang="scss" scoped>
  .vimeography-gallery {
    width: 90%;
    margin: 0 auto 1rem;
  }
</style>
