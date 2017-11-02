import React from 'react';
import VimeoPlayer from '@vimeo/player';

class Player extends React.Component {

  constructor(props) {
    super(props);
    this.loadVideo = this.loadVideo.bind(this);
  }

  componentDidMount() {
    const video = this.props.video;
    this.player = new VimeoPlayer(this.playerContainer, { url: video.link, responsive: true });
  }

  loadVideo(videoId) {
    this.player.loadVideo(videoId).then(function(id) {
      // the video successfully loaded
    }).catch(function(error) {
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

  render() {
    return(
      <div className="vimeography-player" ref={(el) => this.playerContainer = el}></div>
    );
  }
}

export default Player;