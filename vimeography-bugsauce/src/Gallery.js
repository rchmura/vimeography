import VimeoPlayer from '@vimeo/player'
import Flickity from 'flickity'

class Gallery {

  constructor(gallery) {
    Object.assign(this, gallery)

    this.container = document.querySelector(`#vimeography-gallery-${gallery.id}`)
    this.render()
    this.registerClickHandler()
  }

  render() {
    this.renderContainers()
    this.renderPlayer()
    this.renderThumbnails()
  }

  renderContainers() {
    this.playerContainer    = this.createPlayerContainer()
    this.thumbnailContainer = this.createThumbnailContainer()
    this.thumbnails         = this.thumbnailContainer.getElementsByClassName('.vimeography-thumbnail');

    this.container.appendChild(this.playerContainer)
    this.container.appendChild(this.thumbnailContainer)
  }

  renderPlayer(link) {
    this.player = new VimeoPlayer( this.playerContainer, { url: this.video_set[0].link, responsive: true });
  }

  renderThumbnails() {
    let thumbnails = this.video_set.map(video => this.renderThumbnail(video)).join('')

    this.thumbnailContainer.innerHTML += thumbnails

    let flickity = new Flickity( this.thumbnailContainer, {
      cellAlign: 'left',
      contain: true,
      pageDots: false,
      imagesLoaded: true
    });
  }

  renderThumbnail(video) {
    console.dir(video);
    return `
      <figure class="vimeography-thumbnail">
        <a href="${video.link}" title="${video.name}" data-uri="${video.uri}" data-video-width="${video.width}" data-video-height="${video.height}">
          <img alt="${video.name}" src="${video.pictures[2].link}">
        </a>
      </figure>
    `
  }

  registerClickHandler() {
    this.thumbnailContainer.addEventListener('click', event => this.onClick(event))
  }

  onClick(e) {
    e.preventDefault()
    let el = e.path.find((node) => { return node.tagName == "A" });
    let id = el.dataset.uri.replace(/\D/g,'');

    this.player.loadVideo(id).then(function(video_id) {
      console.log(video_id)
        // the video successfully loaded
    }).catch(function(error) {
      console.log(error)
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

  // Common method for all themes
  createThumbnailContainer() {
    let thumbnailContainer = document.createElement('div')
    thumbnailContainer.classList.add('vimeography-thumbnails')
    return thumbnailContainer
  }

  createPlayerContainer() {
    let playerContainer = document.createElement('div')
    playerContainer.classList.add('vimeography-player')
    return playerContainer
  }
}

export default Gallery