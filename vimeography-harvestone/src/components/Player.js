import { mapActions } from 'vuex'
import VimeoPlayer from '@vimeo/player';

const Player = {
  template: `
    <div class="vimeography-player"></div>
  `,
  props: ['url'],
  mounted: function() {
    this.player = new VimeoPlayer(this.$el, { url: this.url, responsive: true });
    this.player.ready().then( () => this.playerReady(this.player) );
    this.player.on('play', data => this.playerPlay(data) );
    this.player.on('pause', data => this.playerPause(data) );
    this.player.on('ended', data => this.playerEnded(data) );
    this.player.on('timeupdate', data => this.playerTimeUpdate(data) );
    this.player.on('progress', data => this.playerProgress(data) );
    this.player.on('seeked', data => this.playerSeeked(data) );
    this.player.on('volumechange', data => this.playerVolumeChange(data) );
    this.player.on('loaded', data => this.playerLoaded(data) );
  },
  watch: {
    url: function(nextUrl, prevUrl) {
      let id = nextUrl.replace(/\D/g,'');
      this.loadVideo(id);
    }
  },
  methods: {
    ...mapActions([
      'playerReady',
      'playerPlay',
      'playerPause',
      'playerEnded',
      'playerTimeUpdate',
      'playerProgress',
      'playerSeeked',
      'playerVolumeChange',
      'playerLoaded'
    ]),
    loadVideo (videoId) {

      this.player.loadVideo(videoId).then( id => {
        // the video successfully loaded
        this.player.getVideoHeight().then( height => {
          console.log(height);
          this.player.set('videoHeight', height);
        });

      }).catch( error => {
        switch (error.name) {
          case 'TypeError':
            // the id was not a number
            break;

          case 'PasswordError':
            // the video is password-protected and the viewer needs to enter the
            // password first
            break;

          case 'PrivacyError':
            // the video is password-protected or private
            break;

          default:
            // some other error occurred
            break;
        }
      });

    }
  }
}

export default Player;
