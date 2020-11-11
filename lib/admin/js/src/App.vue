<template>
  <div id="vimeography_admin_root">
    <Menu :tabs="menuTabs" />

    <DuplicateGallery
      v-if="modalOpen"
      @close-modal="modalOpen = false"
      :gallery-to-duplicate="galleryToDuplicate"
    />

    <router-view @duplicate-gallery="initDuplicateGallery"></router-view>
  </div>
</template>

<script>
import Menu from "./components/Menu.vue";
import DuplicateGallery from "./components/Galleries/Duplicate.vue";

export default {
  name: "App",
  components: {
    Menu,
    DuplicateGallery,
  },
  data() {
    return {
      modalOpen: false,
      galleryToDuplicate: null,
    };
  },
  methods: {
    initDuplicateGallery(gallery) {
      this.galleryToDuplicate = gallery;
      this.modalOpen = true;
    },
  },
  created: function() {
    // `this` points to the vm instance
    // console.log('a is: ' + this.a)
    // console.log(this.$router.currentRoute);
  },
};
</script>

<style scoped>
#vimeography_admin_root {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: left;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
