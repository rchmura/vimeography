window.vimeography = window.vimeography || {};

//http://jsfiddle.net/elijahmanor/MVNBz/22/
(function(utilities, $, undefined) {

  utilities.enable_byline   = 0;
  utilities.enable_title    = 0;
  utilities.enable_portrait = 0;
  utilities.enable_autoplay = 0;
  utilities.enable_api      = 1;

  utilities.player_id = '';

  /**
   * Return the gallery wrapper for the provided ID
   *
   * @since 1.2.3
   * @param  {[type]} id [description]
   * @return {[type]}    [description]
   */
  utilities.get_gallery = function(id) {
    return $('#vimeography-gallery-' + id);
  };

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
  utilities.enable_playlist = function(gallery_id) {
    $gallery = utilities.get_gallery(gallery_id);

    // Wait for the gallery to send a signal
    $gallery.on('vimeography/video/ready', function(){
      var player = $('#' + utilities.player_id)[0];

      $f(player).addEvent('ready', function(player_id){
        var froogaloop = $f(player_id);

        froogaloop.addEvent('finish', function(player_id){
          var gallery = utilities.get_gallery(gallery_id);
          gallery.trigger('vimeography/playlist/next');
        });
      });
    });
  };

  /**
   * [get_video description]
   * @param  {[type]} link [description]
   * @return {[type]}      [description]
   */
  utilities.get_video = function(link) {
    var endpoint = 'https://vimeo.com/api/oembed.json';

    // Put together the URL
    var url = endpoint
    + '?url='      + encodeURIComponent(link)
    + '&byline='   + utilities.enable_byline
    + '&title='    + utilities.enable_title
    + '&portrait=' + utilities.enable_portrait
    + '&autoplay=' + utilities.enable_autoplay
    + '&api='      + utilities.enable_api
    + '&player_id='+ 'vimeography' + Math.floor(Math.random()*999999)
    + '&callback=?';

    return $.getJSON(url);
  };

  /**
   * Sets the iframe DOM ID to the same string that was generated
   * during the oEmbed get_video call
   *
   * @param  string html   Unmodified iframe HTML
   * @return string html   iframe HTML with ID added
   */
  utilities.set_video_id = function(html) {
    var regex = /player_id=(vimeography\d+)/g;
    var match = regex.exec(html);

    var iframe = $(html).filter('iframe')[0];

    utilities.player_id = match[1];
    iframe.id = utilities.player_id;

    return iframe;
  };

  /**
   * [ description]
   * @param  {[type]} html [description]
   * @return {[type]}      [description]
   */
  utilities.add_fancybox_class = function(html) {
    var iframe = $(html).filter('iframe')[0];
    iframe.className = 'fancybox-iframe';
    return iframe;
  };

}( window.vimeography.utilities = window.vimeography.utilities || {}, jQuery ));