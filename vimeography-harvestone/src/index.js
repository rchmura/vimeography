__webpack_public_path__ = window.vimeographyBuildPath;

import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import Gallery from './components/Gallery.vue';
import { store } from 'vimeography-blueprint';

// import store from './store';
import head from 'lodash/head';

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
const render = (Component, galleryId) => {

  const mount = `#vimeography-gallery-${galleryId} > div`;
  const gallery = window.vimeography2.galleries.harvestone[galleryId];
  const firstVideoId = head( gallery.order );
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
    // components: {
    //   Gallery
    // }
    render: h => h(Component)
  });
}

for (let id in window.vimeography2.galleries.harvestone) {
  render(Gallery, id);
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