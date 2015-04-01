window.vimeography = window.vimeography || {};
window.vimeography.appearance_controls = window.vimeography.appearance_controls || {};


/**
 * Set up the global namespace constructor for the appearance settings
 *
 * @param  object gallery_settings Object literal of all gallery settings.
 * @return object
 */
window.vimeography.appearance_controls.numeric = function (control_settings) {
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
window.vimeography.appearance_controls.numeric.prototype.locate_elements = function () {
  this.$control = jQuery('#' + this.control_settings.id);
};


/**
 * Hooks up the necessary event callbacks
 *
 * @return void
 */
window.vimeography.appearance_controls.numeric.prototype.setup_events = function () {
  this.create_numeric();
  this.set_default_values();
};


/**
 * [create_numeric description]
 * @return {[type]} [description]
 */
window.vimeography.appearance_controls.numeric.prototype.create_numeric = function () {
  var _self = this;
  _self.$numeric = this.$control.kendoNumericTextBox({
    format: "#px",
    min: _self.control_settings.min,
    max: _self.control_settings.max,
    step: _self.control_settings.step,
    spin: function() {
      var value = this.value();

      jQuery.each(_self.control_settings.properties, function (index, prop) {
        var attr = prop.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
        var rule = {};
        rule[attr] = value + 'px';

        vein.inject( prop.target, rule );
      });

      if (typeof _self.control_settings.expressions != 'undefined') {
        jQuery.each(_self.control_settings.expressions, function (index, exp) {
          var attr = exp.attribute.replace(/[A-Z]/g, function(a) {return '-' + a.toLowerCase()});
          var rule = {};

          rule[attr] = Math.ceil(_self.operators[exp.operator](value, math.eval(exp.value))) + 'px';

          vein.inject( exp.target, rule );
        });
      }
    },
    change: function() {
      var value = this.value();
      _self.$control.attr('value', value + 'px');

      if (typeof _self.control_settings.expressions != 'undefined') {
        jQuery.each(_self.control_settings.expressions, function (index, exp) {

          jQuery('input[name="vimeography_theme_settings[' + _self.control_settings.id + '][expressions][' + exp.target + '][' + exp.attribute + ']"]')
            .val((Math.ceil(_self.operators[exp.operator](value, math.eval(exp.value)))) + 'px');

        });
      }
    }
  }).data('kendoNumericTextBox');
}


/**
 * [set_default_values description]
 */
window.vimeography.appearance_controls.numeric.prototype.set_default_values = function () {
  var _self = this;

  jQuery(window).load(function() {
    jQuery.each(_self.control_settings.properties, function (index, prop) {
      
      var value = _self.get_custom_style_value(prop.target, prop.attribute);

      if (typeof value === 'undefined') {
        value = jQuery(prop.target).last().css(prop.attribute);
      }

      if (typeof value != 'undefined') {
        var re = new RegExp("px");
        value = value.replace(re, '');
        _self.$numeric.value(value);
        _self.$numeric.trigger('change', { value: value });
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
window.vimeography.appearance_controls.numeric.prototype.get_custom_style_value = function (target, attribute) {

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
  $.each(window.vimeography.gallery_appearance.numeric, function (index, control_settings){
    new window.vimeography.appearance_controls.numeric(control_settings);
  });
});