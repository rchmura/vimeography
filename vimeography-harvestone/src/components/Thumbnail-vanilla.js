import { mapState } from 'vuex'

const Thumbnail = {
  template: `
    <div class="vimeography-thumbnail">
      {{video.name}}
      <img :src="thumbnailUrl" :title="video.name" />
    </div>
  `,
  props: ['video'],
  computed: {
    thumbnailUrl: (data) => {
      return data.video.pictures.filter(img => img.width === 640)[0].link
    }
  }
}

export default Thumbnail;
