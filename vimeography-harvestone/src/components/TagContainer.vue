<script>
import { mapState } from 'vuex'
import Tag from './Tag.vue';

const template = `
  <div class="vimeography-tag-container">
    <tag v-for="tag in tags" :key="tag.name" :name="tag.name" :total="tag.total"></tag>
  </div>
`;

const TagContainer = {
  template,
  computed: mapState({
    tags: state => state.gallery.tags,
  }),
  components: {
    Tag,
  },
}

export default TagContainer;
</script>

<style lang="scss" scoped>
  .container {
    flex: 1;
  }
</style>