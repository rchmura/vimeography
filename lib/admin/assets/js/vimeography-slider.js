window.vimeography = window.vimeography || {};
window.vimeography.appearance_controls = window.vimeography.appearance_controls || {};


/**
 * Set up the global namespace constructor for the appearance settings
 *
 * @param  object gallery_settings Object literal of all gallery settings.
 * @return object
 */
window.vimeography.appearance_controls.slider = function (control_settings) {
  this.control_settings = control_settings;

  this.operators = {
    '+': function(a, b) { return a + b },
    '-': function(a, b) { return a - b },
    '/': function(a, b) { return a / b },
    '*': function(a, b) { return a * b }  
  }

  this.locate_elements();
  this.setup_events();
};


/**
 * Finds the Vimeography theme elements and assigns them to properties
 *
 * @param  int gallery_id The unique id of the gallery currently being displayed.
 * @return void
 */
window.vimeography.appearance_controls.slider.prototype.locate_elements = function () {
  this.$control = jQuery('#' + this.control_settings.id);
};


/**
 * Hooks up the necessary event callbacks
 *
 * @return void
 */
window.vimeography.appearance_controls.slider.prototype.setup_events = function () {
  this.create_slider();
  this.set_default_values();
};


/**
 * [create_slider description]
 * @return {[type]} [description]
 */
window.vimeography.appearance_controls.slider.prototype.create_slider = function () {
  var _self = this;
  _self.$slider = this.$control.kendoSlider({
    increaseButtonTitle: "Right",
    decreaseButtonTitle: "Left",
    min: parseInt(_self.control_settings.min),
    max: parseInt(_self.control_settings.max),
    smallStep: parseInt(_self.control_settings.step),
    largeStep: 1,
    slide: function(e) {
      _self.update_live_preview(e.value);
    },
    change: function(e) {
      _self.update_live_preview(e.value);

      var value = e.value;
      _self.$control.attr('value', value + 'px');

      if (typeof _self.control_settings.expressions != 'undefined') {
        jQuery.each(_self.control_settings.expressions, function (index, exp) {

          jQuery('input[name="vimeography_theme_settings[' + _self.control_settings.id + '][expressions][' + exp.target + '][' + exp.attribute + ']"]')
            .val((Math.ceil(_self.operators[exp.operator](value, math.eval(exp.value)))) + 'px');

        });
      }
    }
  }).data('kendoSlider');
}

window.vimeography.appearance_controls.slider.prototype.update_live_preview = function (value) {
  var _self = this;

  jQuery.each(_self.control_settings.properties, function (index, prop) {
    var attr = prop.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
    var rule = {};
    var computed = prop.transform ? prop.transform.replace(/{{value}}/, value + 'px') : value + 'px';

    rule[attr] = computed;

    vein.inject( prop.target, rule );
  });

  if (typeof _self.control_settings.expressions != 'undefined') {
    jQuery.each(_self.control_settings.expressions, function (index, exp) {
      var attr = exp.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
      var rule = {};
      var calculated = Math.ceil(_self.operators[exp.operator](value, math.eval(exp.value)));
      var computed = exp.transform ? exp.transform.replace(/{{value}}/, calculated + 'px') : calculated + 'px';

      rule[attr] = computed;
      vein.inject( exp.target, rule );
    });
  }
}


/**
 * [set_default_values description]
 */
window.vimeography.appearance_controls.slider.prototype.set_default_values = function () {
  var _self = this;

  jQuery(window).load(function() {
    jQuery.each(_self.control_settings.properties, function (index, prop) {
      
      var value = _self.get_custom_style_value(prop.target, prop.attribute);

      if (typeof value === 'undefined') {
        // If the attribute value has more than one value, use the first one.
        value = jQuery(prop.target).last().css(prop.attribute).split(' ')[0];
      }

      if (typeof value != 'undefined') {
        value = parseInt(value, 10);

        _self.$slider.value(value);
        _self.$slider.trigger('change', { value: value });
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
window.vimeography.appearance_controls.slider.prototype.get_custom_style_value = function (target, attribute) {

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
  $.each(window.vimeography.gallery_appearance.slider, function (index, control_settings){
    new window.vimeography.appearance_controls.slider(control_settings);
  });
});