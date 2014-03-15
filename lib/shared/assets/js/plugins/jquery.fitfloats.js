(function( $ ) {

  $.fn.fitFloats = function() {
    var container = this;

    function change_container_size(container)
    {
      var container_width = container.width();
      var parent_width = container.parent().width();
      var number_of_items = container.children().length; // Number of items in parent container
      var item_width = container.children().eq(0).outerWidth(true);

      var new_width;

      var max_items_per_row = Math.floor(parent_width / item_width);

      if (number_of_items < max_items_per_row)
      {
        new_width = item_width * number_of_items;
      }
      else
      {
        new_width = item_width * max_items_per_row;
      }

      if (parent_width > container_width + item_width && number_of_items > max_items_per_row)
      {
        new_width = new_width + item_width;
      }

      container.width(new_width);
    }

    change_container_size(container);

    $(window).resize(function() {
      change_container_size(container);
    });
  };

})( jQuery );