import { mapState } from 'vuex'

import Player from './Player.js';
import ThumbnailContainer from './ThumbnailContainer.vue';

const Gallery = {
  data: () => {
    return {
      message: 'hello'
    }
  },
  template: `
    <div>
      <player :url="this.activeVideo"></player>
      A custom gallery component! {{message}} and {{theme}}
      <thumbnail-container :videos="videos"></thumbnail-container>
    </div>
  `,
  computed: mapState({
    theme: state => state.theme,
    videos: state => state.video_set,
    activeVideo: state => state.activeVideo
  }),
  components: {
    Player,
    ThumbnailContainer
  }
}

export default Gallery;
