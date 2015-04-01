window.vimeography = window.vimeography || {};
window.vimeography.appearance_controls = window.vimeography.appearance_controls || {};


/**
 * Set up the global namespace constructor for the appearance settings
 *
 * @param  object gallery_settings Object literal of all gallery settings.
 * @return object
 */
window.vimeography.appearance_controls.colorpicker = function (control_settings) {
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
window.vimeography.appearance_controls.colorpicker.prototype.locate_elements = function () {
  this.$control = jQuery('#' + this.control_settings.id);
};


/**
 * Hooks up the necessary event callbacks
 *
 * @return void
 */
window.vimeography.appearance_controls.colorpicker.prototype.setup_events = function () {
  this.create_colorpicker();
  this.set_default_values();
};


/**
 * [create_colorpicker description]
 * @return {[type]} [description]
 */
window.vimeography.appearance_controls.colorpicker.prototype.create_colorpicker = function () {
  var _self = this;
  _self.$colorpicker = this.$control.kendoColorPicker({
    palette: null,
    buttons: false,
    select: function(e) {
      jQuery.each(_self.control_settings.properties, function (index, prop) {
        var attr = prop.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
        var rule = {};
        rule[attr] = e.value;

        vein.inject( prop.target, rule );
      });
    },
    change: function(e) {
      _self.$control.attr('value', e.value);
    }
  }).data('kendoColorPicker');
}


/**
 * [set_default_values description]
 */
window.vimeography.appearance_controls.colorpicker.prototype.set_default_values = function () {
  var _self = this;

  jQuery(window).load(function() {

    jQuery.each(_self.control_settings.properties, function (index, prop) {

      var value = _self.get_custom_style_value(prop.target, prop.attribute);

      if (typeof value === 'undefined') {
        value = jQuery(prop.target).last().css(prop.attribute);
      }

      if (typeof value != 'undefined') {
        value = kendo.parseColor( value, true ).toCss();
        _self.$colorpicker.value(value);
        _self.$control.attr('value', value);
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
window.vimeography.appearance_controls.colorpicker.prototype.get_custom_style_value = function (target, attribute) {

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

  /** Make sure all colors, backgroundColors, and borderColors are being captured as HEX */
  $.cssHooks.color = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["color"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.cssHooks.borderColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["borderColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("border-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.cssHooks.borderLeftColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["borderLeftColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("border-left-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.cssHooks.borderRightColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["borderRightColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("border-right-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.cssHooks.borderTopColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["borderTopColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("border-top-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.cssHooks.borderBottomColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["borderBottomColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("border-bottom-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  /** RGBA regex for backgroundColor */
  $.cssHooks.backgroundColor = {
      get: function(elem) {
          if (elem.currentStyle)
              var bg = elem.currentStyle["backgroundColor"];
          else if (window.getComputedStyle)
              var bg = document.defaultView.getComputedStyle(elem,
                  null).getPropertyValue("background-color");
          if (bg.search("rgb") == -1)
              return bg;
          else {
              bg = bg.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
              function hex(x) {
                  return ("0" + parseInt(x).toString(16)).slice(-2);
              }
              return "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3]);
          }
      }
  }

  $.each(window.vimeography.gallery_appearance.colorpicker, function (index, control_settings){
    new window.vimeography.appearance_controls.colorpicker(control_settings);
  });
});