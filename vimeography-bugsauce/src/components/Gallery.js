import React from 'react';
import Player from './Player';
import Thumbnail from './Thumbnail';

import Flickity from 'flickity-imagesloaded';

class Gallery extends React.Component {
  constructor(props) {
    super(props);
    this.state = { ...props.details };

    this.loadVideo = this.loadVideo.bind(this);
    this.maybePaginate = this.maybePaginate.bind(this);
  }

  componentDidMount() {
    this.flickity = new Flickity( this.thumbnailContainer, {
      cellAlign: 'left',
      contain: true,
      pageDots: false,
      groupCells: true,
      imagesLoaded: true
    });

    this.flickity.on( 'scroll', (progress) => this.maybePaginate(progress) );
  }

  maybePaginate(progress) {
    if (progress < 0.8) {
      return false;
    }

    if (this.isPaging) {
      return false;
    }
  }

  // addVideo(video) {
  //   const videos = {...this.state.videos};
  //   videos[`video-${video.id}`] = video;
  //   this.setState({ videos });
  // }

  loadVideo(event, index) {
    event.preventDefault();

    const video = this.state.video_set[index];
    const videoId = video.uri.replace(/\D/g,'');

    this.flickity.selectCell( parseInt(index) );
    this.player.loadVideo(videoId);

    // this.context.router.transitionTo(`/vimeography/${videoId}`);
  }

  render() {
    return(
      <div>
        <Player video={this.state.video_set[0]} ref={(player) => this.player = player} />
        <div className="vimeography-thumbnails" ref={(el) => this.thumbnailContainer = el}>
          {
            Object
              .keys(this.state.video_set)
              .map(key => <Thumbnail key={key} index={key} video={this.state.video_set[key]} loadVideo={this.loadVideo} />)
          }
        </div>
      </div>
    );
  }
}

Gallery.contextTypes = {
  router: React.PropTypes.object
};

export default Gallery;