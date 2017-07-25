<template>
  <figure>
    <img class="vimeography-thumbnail" :src="thumbnailUrl" :title="video.name" @click.prevent="loadVideo(video.link)" />
  </figure>
</template>

<script>
import { mapState, mapActions } from 'vuex'

const Thumbnail = {
  props: ['video'],
  computed: {
    thumbnailUrl: (data) => {
      return data.video.pictures.filter(img => img.width === 640)[0].link
    }
  },
  methods: {
    ...mapActions([
      'loadVideo'
    ])
  }
}

export default Thumbnail;
</script>

<style scoped>
  figure {
    margin: 0 10px 0 0;
  }

  .vimeography-thumbnail {
    border-radius: 4px;
    max-width: 200px;
    cursor: pointer;
  }
</style>
