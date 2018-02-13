<script>
import { mapState } from 'vuex'

const defaultTemplate = `
  <figure class="swiper-slide vimeography-thumbnail">
    <router-link class="vimeography-link" :to="this.query" exact exact-active-class="vimeography-link-active">
      <img class="vimeography-thumbnail-img" :src="thumbnailUrl" :title="video.name" />
    </router-link>
  </figure>
`;

const userTemplate = document.querySelector('#vimeography-harvestone-thumbnail');

const Thumbnail = {
  props: ['video'],
  template: userTemplate ? userTemplate.innerText : defaultTemplate,
  computed: {
    query() {
      const q = {
        ...this.$route.query,
        vimeography_gallery: this.$store.state.gallery.id,
        vimeography_video: this.video.id
      };

      return '?' + Object.keys(q).map(k => k + '=' + encodeURIComponent(q[k])).join('&')
    },
    thumbnailUrl: (data) => {
      const selections = data.video.pictures.sizes.filter(img => img.width <= 640)
      const sorted = selections.sort( (a, b) => a.width - b.width )
      return sorted[sorted.length - 1].link
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
    border: 1px solid #cccccc;
    border-radius: 4px;
  }

  .vimeography-link-active {
    border: 1px solid #5580e6;
  }

  .vimeography-thumbnail-img {
    border-radius: 4px;
    max-width: 198px;
    cursor: pointer;
  }
</style>
