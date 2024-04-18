window.vimeography = window.vimeography || {};


/**
 * [utilities2 description]
 * @return {[type]} [description]
 */
window.vimeography.utilities2 = function() {
  this.enable_byline   = 0;
  this.enable_title    = 0;
  this.enable_portrait = 0;
  this.enable_autoplay = 0;
  this.enable_api      = 1;

  this.player_id = '';
};


/**
 * Return the gallery wrapper for the provided ID
 *
 * @since 1.2.3
 * @param  {[type]} id [description]
 * @return {[type]}    [description]
 */
window.vimeography.utilities2.prototype.get_gallery = function( id ) {
  return jQuery('#vimeography-gallery-' + id);
}


/**
 * Sets a custom event to trigger when the Vimeo video
 * has ended
 *
 * If any videos exist, add events
 *
 * If no videos, we need to add events when they are finally retrieved
 *
 * @since  1.2.3
 * @return {[type]} [description]
 */
window.vimeography.utilities2.prototype.enable_playlist = function(gallery_id) {
  var _self = this;
  $gallery = _self.get_gallery(gallery_id);

  /* Wait for the gallery to send a signal */
  $gallery.on('vimeography/video/ready', function(){
    var player = jQuery('#' + _self.player_id)[0];

    $f(player).addEvent('ready', function(player_id){
      var froogaloop = $f(player_id);

      froogaloop.addEvent('finish', function(player_id){
        var gallery = _self.get_gallery(gallery_id);
        gallery.trigger('vimeography/playlist/next');
      });
    });
  });
}


/**
 * [get_video description]
 * @param  {[type]} link [description]
 * @return {[type]}      [description]
 */
window.vimeography.utilities2.prototype.get_video = function(link) {
  var endpoint = 'https://vimeo.com/api/oembed.json';

  /* Put together the URL */
  var url = endpoint
  + '?url='      + encodeURIComponent(link)
  + '&byline='   + this.enable_byline
  + '&title='    + this.enable_title
  + '&portrait=' + this.enable_portrait
  + '&autoplay=' + this.enable_autoplay
  + '&api='      + this.enable_api
  + '&player_id='+ 'vimeography' + Math.floor(Math.random()*999999)
  + '&callback=?';

  return jQuery.getJSON(url);
}


/**
 * Sets the iframe DOM ID to the same string that was generated
 * during the oEmbed get_video call
 *
 * @param  string html   Unmodified iframe HTML
 * @return string html   iframe HTML with ID added
 */
window.vimeography.utilities2.prototype.set_video_id = function(html) {
  var regex = /player_id=(vimeography\d+)/g;
  var match = regex.exec(html);

  var iframe = jQuery(html).filter('iframe')[0];

  this.player_id = match[1];
  iframe.id = this.player_id;

  return iframe;
}


/**
 * [ description]
 * @param  {[type]} html [description]
 * @return {[type]}      [description]
 */
window.vimeography.utilities2.prototype.add_fancybox_class = function(html) {
  var iframe = jQuery(html).filter('iframe')[0];
  iframe.className = 'fancybox-iframe';
  return iframe;
}

/**
 * Detect if the current browser is IE11
 * @return {Boolean} [description]
 */
window.vimeography.utilities2.prototype.is_ie11 = function () {
  return !(window.ActiveXObject) && "ActiveXObject" in window;
}