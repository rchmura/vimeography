window.vimeography = window.vimeography || {};
window.vimeography.appearance_controls = window.vimeography.appearance_controls || {};


/**
 * Set up the global namespace constructor for the appearance settings
 *
 * @param  object gallery_settings Object literal of all gallery settings.
 * @return object
 */
window.vimeography.appearance_controls.visibility = function (control_settings) {
  this.control_settings = control_settings;

  this.locate_elements();
  this.setup_events();
};


/**
 * Finds the Vimeography theme elements and assigns them to properties
 *
 * @param  int gallery_id The unique id of the gallery currently being displayed.
 * @return void
 */
window.vimeography.appearance_controls.visibility.prototype.locate_elements = function () {
  this.$control = jQuery('#' + this.control_settings.id);
  this.$checkbox = jQuery('#' + this.control_settings.id + '-checkbox');
};


/**
 * Hooks up the necessary event callbacks
 *
 * @return void
 */
window.vimeography.appearance_controls.visibility.prototype.setup_events = function () {
  this.set_default_values();
};


/**
 * [set_default_values description]
 */
window.vimeography.appearance_controls.visibility.prototype.set_default_values = function () {
  var _self = this;

  jQuery(window).load( function () {

    jQuery.each(_self.control_settings.properties, function (index, prop) {
      var check_val = _self.get_custom_style_value(prop.target, prop.attribute);
      if (typeof check_val === 'undefined') {
        check_val = jQuery(prop.target).last().css(prop.attribute);
      }

      if (typeof check_val != 'undefined') {
        if (check_val == 'block') {
          _self.$checkbox.prop('checked', true);
          _self.$control.val('block');
        } else if (check_val == 'none') {
          _self.$checkbox.prop('checked', false);
          _self.$control.val('none');
        }
      } else {
        /* Default to checked */
        _self.$checkbox.prop('checked', true);
        _self.$control.val('block');
      }
    });

    _self.$checkbox.change(function () {
      if ( jQuery(this).is(':checked') ) {
        jQuery.each(_self.control_settings.properties, function (index, prop) {
          vein.inject( prop.target, {'display' : 'block'} );
        });

        _self.$control.val('block');

      } else {
        jQuery.each(_self.control_settings.properties, function (index, prop) {
          vein.inject( prop.target, {'display' : 'none'} );
        });

        _self.$control.val('none');
      }
    });

  });
}

/**
 * Gets styles by a classname and attribute
 * 
 * @notice The className must be 1:1 the same as in the CSS
 * @param string target classname
 * @param string attribute attribute
 * @return string value
 */
window.vimeography.appearance_controls.visibility.prototype.get_custom_style_value = function (target, attribute) {

  var styleSheets = window.document.styleSheets;
  var styleSheetsLength = styleSheets.length;
  for(var i = 0; i < styleSheetsLength; i++){
    if (styleSheets[i].href !== null && styleSheets[i].href.indexOf("vimeography-gallery") > -1) {
      var classes = styleSheets[i].rules || styleSheets[i].cssRules;
      var classesLength = classes.length;
      for (var x = 0; x < classesLength; x++) {
        var re = new RegExp("^#vimeography-gallery-\\d+");
        var matcher = classes[x].selectorText.replace(re, '');
        
        if (matcher === target && classes[x].style[attribute].length > 0) {
          return classes[x].style[attribute];
        }
      }
    }
  }
}


/**
 * Invoke the constructor for each Vimeography gallery setting
 *
 * @param  jQuery
 * @return void
 */
jQuery(function( $ ) {
  $.each(window.vimeography.gallery_appearance.visibility, function (index, control_settings){
    new window.vimeography.appearance_controls.visibility(control_settings);
  });
});