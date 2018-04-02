<script>
import { mapState } from 'vuex'
import { Mixins } from 'vimeography-blueprint'

const defaultTemplate = `
  <figure :class="this.thumbnailClass">
    <router-link class="vimeography-link" :title="video.name" :to="this.query" exact exact-active-class="vimeography-link-active">
      <img class="vimeography-thumbnail-img" :src="thumbnailUrl" :alt="video.name" />
    </router-link>
  </figure>
`;

const userTemplate = document.querySelector('#vimeography-harvestone-thumbnail');

const Thumbnail = {
  props: ['video'],
  mixins: [Mixins.Thumbnail],
  template: userTemplate ? userTemplate.innerText : defaultTemplate,
  computed: {
    thumbnailClass() {
      return `swiper-slide vimeography-thumbnail vimeography-video-${this.video.id}`
    },
    query() {
      const q = {
        ...this.$route.query,
        vimeography_gallery: this.$store.state.gallery.id,
        vimeography_video: this.video.id
      };

      return '?' + Object.keys(q).map(k => k + '=' + encodeURIComponent(q[k])).join('&')
    },
    ...mapState({
      galleryId: state => state.id
    })
  }
}

export default Thumbnail;
</script>

<style lang="scss" scoped>
  .vimeography-thumbnail {
    margin: 0;
    max-width: 200px;
  }

  .vimeography-link {
    display: block;
    font-size: 0;
    line-height: 0;
    border-radius: 4px;
    box-shadow: none;

    img {
      border: 1px solid #cccccc;
    }
  }

  .vimeography-link-active img {
    border: 1px solid #5580e6;
  }

  .vimeography-thumbnail-img {
    border-radius: 4px;
    max-width: 198px;
    cursor: pointer;
  }
</style>
