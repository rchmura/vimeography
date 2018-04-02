__webpack_public_path__ = window.vimeographyBuildPath;

import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import VueObserveVisibility from 'vue-observe-visibility';

Vue.use(Vuex);
Vue.use(VueRouter);
Vue.use(VueObserveVisibility);

import Gallery from './components/Gallery.vue';
import { storeModules } from 'vimeography-blueprint';

import head from 'lodash/head';
import cloneDeep from 'lodash/cloneDeep';
var URLSearchParams = require('url-search-params');

const router = new VueRouter({
  mode: window.vimeographyRouterMode,
  routes: [
    { path: '/', component: Gallery, props: true, name: 'gallery' }
  ]
});

let params = new URLSearchParams(location.search.slice(1));

/**
 * https://github.com/webpack/webpack-dev-server/issues/100
 * @param  {[type]} Component [description]
 * @return {[type]}           [description]
 */
const render = (Component, galleryId, store) => {

  // We use concatenation here so Vimeography Blueprint doesn't get confused
  // with this line when generating new themes.
  const mount = '#vimeography-gallery-' + galleryId + ' > div';
  const gallery = window.vimeography2.galleries.harvestone[galleryId];
  const firstVideoId = head( gallery.pages.default[Object.keys(gallery.pages.default)[0]] );
  const activeVideoId = params.get('vimeography_video') && params.get('vimeography_gallery') == galleryId ? parseInt( params.get('vimeography_video') ) : parseInt( firstVideoId );

  store.commit({
    type: 'vimeography/gallery/LOAD',
    ...gallery,
    activeVideoId
  })

  new Vue({
    el: mount,
    store,
    router,
    render: h => h(Component)
  });
}

for (let id in window.vimeography2.galleries.harvestone) {
  let store = new Vuex.Store({ modules: cloneDeep( storeModules ) });
  render(Gallery, id, store);
}

// Set default page route, if applicable
// router.replace('vimeography');

// This also works.

// if (module.hot) {
//   module.hot.accept('./components/Gallery', () => {
//     const NextGallery = require('./components/Gallery').default
//     render(NextGallery)
//   })
// }

if (module.hot) {
  module.hot.accept();
}