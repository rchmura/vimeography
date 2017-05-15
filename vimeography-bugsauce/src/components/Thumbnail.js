import React from 'react';

class Thumbnail extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { video, index } = this.props;

    return(
      <figure className="vimeography-thumbnail" onClick={(e) => this.props.loadVideo(e, index) }>
        <a href={video.link} title={video.name}>
          <img alt={video.name} src={video.pictures[2].link} />
        </a>
      </figure>
    );
  }
}

export default Thumbnail;