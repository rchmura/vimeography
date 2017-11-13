<script>
  /*
    Theme-specific at the moment. Perhaps you can just override
    the template from within the theme for this file?
   */
  import { mapState, mapActions } from 'vuex'

  import Filters from './Filters.vue';
  import { Player } from 'vimeography-blueprint';

  import ThumbnailContainer from './ThumbnailContainer.vue';

  const template = `
    <div class="vimeography-gallery">
      <filters></filters>
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
        if (to.query.vimeography_video) {
          this.loadVideo(to.query.vimeography_video)
        }
      }
    },
    computed: {
      ...mapState({
        activeVideo: state => state.videos.items[state.videos.active]
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
