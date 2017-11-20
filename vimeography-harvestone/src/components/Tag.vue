<script>
import { mapState, mapActions, mapGetters } from 'vuex'

const template = `
  <router-link :to="query">
    <span class="vimeography-tag-name">{{name}}</span>
  </router-link>
`;

const Tag = {
  props: ['name', 'total'],
  template,
  computed: {
    query() {
      const q = {
        ...this.$route.query,
        vimeography_gallery: this.$store.state.gallery.id,
        vimeography_tag: this.name
      };

      delete q.vimeography_video;

      return '?' + Object.keys(q).map(k => k + '=' + encodeURIComponent(q[k])).join('&')
    },
  },
  methods: {
    ...mapActions([
      'performSearch'
    ]),
    ...mapGetters([
      'getVideosByTag'
    ])
  }
}

export default Tag;
</script>

<style lang="scss" scoped>
  a {
    background-color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 30px;
    margin: 0 0.5rem 1rem 0;
    display: inline-block;
    font-weight: bold;
    text-decoration: none;
    border: 1px solid #eee;
    outline: none;
    color: #333;
  }
</style>
