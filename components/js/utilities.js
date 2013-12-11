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
  utilities.get_video = function(link)
  {
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
   * [ description]
   * @param  {[type]} html [description]
   * @return {[type]}      [description]
   */
  utilities.set_video_id = function(html)
  {
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
  utilities.add_fancybox_class = function(html)
  {
    var iframe = $(html).filter('iframe')[0];
    iframe.className = 'fancybox-iframe';
    return iframe;
  };

  /**
   * [ description]
   * @param  {[type]} slider [description]
   * @return {[type]}        [description]
   */
  utilities.autoplay_flexslider = function(slider)
  {
    var player = $('#' + utilities.player_id)[0];

    // slider autoplay
    $f(player).addEvent('ready', function(player_id){
      var froogaloop = $f(player_id);

      froogaloop.addEvent('finish', function(player_id){

        var $current_slide = $(slider.slides).filter('li[class*=active-slide]');
        var $new_slide     = $current_slide.next();

        $new_slide.find('img').trigger('click');

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

        if ( $new_slide.index() >= slider.visible * (slider.currentSlide + 1) )
          slider.flexslider('next');
      });
    });

  };

  utilities.autoplay_fancybox = function(fancybox)
  {
    var player = $('#' + utilities.player_id)[0];

    // fancybox autoplay

    $f(player).addEvent('ready', function(player_id){
      var froogaloop = $f(player_id);

      froogaloop.addEvent('finish', function(player_id){
        //$.fancybox.next();
        console.log(fancybox.element);
        fancybox.element.parent().next().find('a').trigger('click');
      });
    });
  };

  /**
   * Utility function for adding an event. Handles the inconsistencies
   * between the W3C method for adding events (addEventListener) and
   * IE's (attachEvent).
   */
  utilities.addEvent = function(element, eventName, callback) {
      if (element.addEventListener) {
          element.addEventListener(eventName, callback, false);
      }
      else {
          element.attachEvent(eventName, callback, false);
      }
  };

}( window.vimeography.utilities = window.vimeography.utilities || {}, jQuery ));