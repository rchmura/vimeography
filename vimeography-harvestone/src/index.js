import Vue from 'vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import VimeoPlayer from '@vimeo/player';

import Gallery from './components/Gallery';
import store from './store/index.js';

const router = new VueRouter({
  routes: [
    { path: '/vimeography/:galleryId/:videoId', component: Gallery }
  ]
});

const app = new Vue({
  el: '#vimeography-gallery-29',
  store,
  router,
  components: {
    Gallery
  }
});
