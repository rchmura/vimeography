window.vimeography = window.vimeography || {};

//http://jsfiddle.net/elijahmanor/MVNBz/22/
(function(pagination, $, undefined) {

  //Public Property
  var paging = {};

  // Private Property
  var pages = {};

    // Public Method
    pagination.paginate = function(element)
    {
      if (! is_multipage())
        return false;

        var go_to = element.data('go-to');

        pagination.request.action   = 'vimeography_pro_ajax_paginate';
        pagination.request.endpoint = $.isEmptyObject(paging) ? pagination.endpoint : paging[go_to];
        pagination.request.page     = get_page( go_to );

        if ( page_exists() )
        {
          console.log(pagination.request);

          var promise = $.ajax({
            type: 'POST',
            url: pagination.request._ajax_url,
            data: pagination.request,
            dataType: 'json'
          });

          promise.done(function(response){
            console.log(response);
            if (response.result == 'success')
            {
              pagination.current_page = response.page;
              paging = response.paging;
              pagination.set_pages();
              pagination.set_paging_controls();

              console.log(paging);
              return true;

            }
            else
            {
              return false;
            } // end error
          });

          return promise;

        }
        else
        {
          console.log('Page does not exist.');
        }
    };

    /**
     * [ description]
     * @return {[type]} [description]
     */
    pagination.set_pages = function() {
      pages = {
        first    : 1,
        last     : Math.ceil(pagination.total / pagination.per_page)
      };
      pages.next     = pagination.current_page < pages.last  ? pagination.current_page + 1 : null;
      pages.previous = pagination.current_page > pages.first ? pagination.current_page - 1 : null;
    };

    /**
     * [description]
     * @return {[type]} [description]
     */
    pagination.set_paging_controls = function() {
        //Private Property
        var disabled = 'vimeography-paging-disabled';
        var container = '#vimeography-gallery-' + pagination.request.gallery_id;

        $('.' + disabled).removeClass( disabled );

        if (pagination.per_page < pagination.total)
          $(container + ' .vimeography-paging-controls').css('display', 'block');

        switch (pagination.current_page)
        {
        case pages.first:
          $(container + ' .vimeography-previous-page, ' + container + ' .vimeography-first-page').addClass( disabled );
          break;
        case pages.last:
          $(container + ' .vimeography-next-page, ' + container + ' .vimeography-last-page').addClass( disabled );
          break;
        default:
          break;
        }
    }

    /**
     * [get_page description]
     * @param  {[type]} index [description]
     * @return {[type]}       [description]
     */
    function get_page( index )
    {
      return pages[index];
    }

    /**
     * [is_multipage description]
     * @return {Boolean} [description]
     */
    function is_multipage()
    {
        return pagination.per_page <= pagination.total;
    }

    /**
     * Don't make the request if it is out of range.
     * @return {[type]} [description]
     */
    function page_exists()
    {
        return pagination.request.page != null;
    }

    //Private Method
    function addItem( item ) {
        if ( item !== undefined ) {
            console.log( "Adding " + $.trim(item) );
        }
    }

}( window.vimeography.pagination = window.vimeography.pagination || {}, jQuery ));