<template>
  <div class="menu">
    <div class="tabs">
      <router-link
        v-for="tab in tabs"
        :key="tab"
        class="tab"
        :to="tabUrl(tab)"
        >{{ tab.charAt(0).toUpperCase() + tab.slice(1) }}</router-link
      >
    </div>
  </div>
</template>

<script>
export default {
  name: "Menu",
  props: {
    tabs: Array,
  },
  methods: {
    tabUrl(name) {
      return `/${name}`;
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.menu {
  margin-bottom: 20px;
  border-bottom: 1px solid #d3d0d4;
  border-top: 1px solid #d3d0d4;
  padding: 0;
}

.tabs {
  display: flex;
  align-items: center;
  width: 100%;
}

.tab {
  padding: 0.65rem 1rem;
  background: #fff;
  color: #333;
  cursor: pointer;
}

.tab:not(:last-child) {
  border-right: 1px solid #dee0e7;
}

.router-link-active {
  color: rgb(51, 99, 170);
  border-bottom: 2px solid rgb(51, 99, 170);
  font-weight: bold;
  border-radius: 0.25rem 0.25rem 0 0;
}
</style>
