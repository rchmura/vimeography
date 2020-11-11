import { createApp } from "vue";
import { router } from "./router";

import App from "./App.vue";

document.addEventListener("DOMContentLoaded", () => {
  const app = createApp(App);

  app.config.globalProperties.menuTabs = ["galleries"];

  app.use(router);

  if (window.vimeography_pro_admin_plugin) {
    app.use(window.vimeography_pro_admin_plugin, router);
  }

  app.mount("#app");
});
