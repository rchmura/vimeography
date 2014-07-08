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

  /**
   * Trigger a click event on the next thumbnail in the flexslider list
   * once the video has finished playing
   *
   * @param  object slider   Flexslider object
   * @return void
   */
  utilities.autoplay_flexslider = function(slider) {
    var player = $('#' + utilities.player_id)[0];

    // slider autoplay
    $f(player).addEvent('ready', function(player_id){
      var froogaloop = $f(player_id);

      froogaloop.addEvent('finish', function(player_id){

        var $current_slide = $(slider.slides).filter(function(index, el) {
          return $(el).find('a.active').length ? true : false;
        });
        var $new_slide     = $current_slide.next();

        $new_slide.find('a').trigger('click');

        // If the index of the newly-active item is greater than what is currently
        // visible, we have to go to the next flexslider page.
        //
        // slider.visible = number of completely visible slides
        // slider.currentSlide = current page
        // slider.slides = jquery object of all items
        //
        // options of interest:
        //console.log(slider.pagingCount); // total number of pages
        //console.log(slider.last); // last page
        //console.log(slider.count); number of slides

        if ( $new_slide.index() >= slider.visible * (slider.currentSlide + 1) ) {
          slider.flexslider('next');
        }
      });
    });

  };

  /**
   * Adds a listener to the froogaloop 'finish' event
   * that initiates the next video in the list
   *
   * @param  object fancybox
   * @return void
   */
  utilities.autoplay_fancybox = function(fancybox) {
    var player = $('#' + utilities.player_id)[0];

    $f(player).addEvent('ready', function(player_id){
      var froogaloop = $f(player_id);

      froogaloop.addEvent('finish', function(player_id){
        //$.fancybox.next();
        // console.log(fancybox.element);
        // Trigger the click event on the next Fancybox thumbnail
        fancybox.element.parent().next().find('a').trigger('click');
      });
    });
  };

}( window.vimeography.utilities = window.vimeography.utilities || {}, jQuery ));