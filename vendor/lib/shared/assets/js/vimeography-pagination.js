window.vimeography = window.vimeography || {};

/**
 * [pagination2 description]
 * @return {[type]} [description]
 */
window.vimeography.pagination2 = function(pagination) {
  this.paging = {};
  this.pages = {};

  this.current_page = pagination.current_page;
  this.endpoint     = pagination.endpoint;
  this.per_page     = pagination.per_page;
  this.total        = pagination.total;
  this.request      = pagination.request;

  this.init();
};

/**
 * [init description]
 * @return {[type]} [description]
 */
window.vimeography.pagination2.prototype.init = function() {
  this.set_pages();
  this.set_paging_controls();
}

/**
 * [paginate description]
 * @param  {[type]} element [description]
 * @return {[type]}         [description]
 */
window.vimeography.pagination2.prototype.paginate = function(element) {
  var _self = this,
          $ = jQuery;

  if (! _self.is_multipage() ) {
    return false;
  }

  var go_to = element.data('go-to');

  _self.request.action   = 'vimeography_pro_ajax_paginate';
  _self.request.endpoint = $.isEmptyObject(_self.paging) ? _self.endpoint : _self.paging[go_to];
  _self.request.page     = _self.get_page( go_to );

  if ( _self.page_exists() ) {
    console.log(_self.request);

    var promise = $.ajax({
      type     : 'POST',
      url      : _self.request._ajax_url,
      data     : _self.request,
      dataType : 'json'
    });

    promise.done(function(response){
      console.log(response);
      if (response.result == 'success') {
        _self.current_page = response.page;
        _self.paging = response.paging;
        _self.set_pages();
        _self.set_paging_controls();

        console.log(_self.paging);
        return true;
      } else {
        return false;
      }
    });

    return promise;

  } else {
    console.log('Page does not exist.');
  }
}

/**
 * [set_pages description]
 */
window.vimeography.pagination2.prototype.set_pages = function() {
  this.pages = {
    first    : 1,
    last     : Math.ceil(this.total / this.per_page)
  };
  this.pages.next     = this.current_page < this.pages.last  ? this.current_page + 1 : null;
  this.pages.previous = this.current_page > this.pages.first ? this.current_page - 1 : null;
}

/**
 * [set_paging_controls description]
 */
window.vimeography.pagination2.prototype.set_paging_controls = function() {
  var _self = this,
          $ = jQuery;

  var disabled = 'vimeography-paging-disabled',
      container = '#vimeography-gallery-' + _self.request.gallery_id;

  $('.' + disabled).removeClass( disabled );

  if (_self.per_page < _self.total) {
    $(container + ' .vimeography-paging-controls').css('display', 'block');
  }

  switch (_self.current_page) {
    case _self.pages.first:
      $(container + ' .vimeography-previous-page, ' + container + ' .vimeography-first-page').addClass( disabled );
      break;
    case _self.pages.last:
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
window.vimeography.pagination2.prototype.get_page = function( index ) {
  return this.pages[index];
}

/**
 * [is_multipage description]
 * @return {Boolean} [description]
 */
window.vimeography.pagination2.prototype.is_multipage = function() {
  return this.per_page <= this.total;
}

/**
 * Don't make the request if it is out of range.
 * @return {[type]} [description]
 */
window.vimeography.pagination2.prototype.page_exists = function() {
  return this.request.page != null;
}