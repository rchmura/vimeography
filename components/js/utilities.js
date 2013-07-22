window.vimeography = window.vimeography || {};

//http://jsfiddle.net/elijahmanor/MVNBz/22/
(function(utilities, $, undefined) {

  utilities.enable_byline   = 0;
  utilities.enable_title    = 0;
  utilities.enable_portrait = 0;
  utilities.enable_autoplay = 0;
  utilities.enable_api      = 1;

  utilities.get_video = function(link)
  {
    var endpoint = 'http://vimeo.com/api/oembed.json';

    // Put together the URL
    var url = endpoint
    + '?url='      + encodeURIComponent(link)
    + '&byline='   + utilities.enable_byline
    + '&title='    + utilities.enable_title
    + '&portrait=' + utilities.enable_portrait
    + '&autoplay=' + utilities.enable_autoplay
    + '&api='      + utilities.enable_api;

    return $.get(url);
  }

}( window.vimeography.utilities = window.vimeography.utilities || {}, jQuery ));