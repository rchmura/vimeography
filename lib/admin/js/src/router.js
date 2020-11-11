import { createRouter, createMemoryHistory } from "vue-router";

import ListGalleries from "./components/Galleries/List.vue";

export const routerHistory = createMemoryHistory();
const routes = [
  {
    path: "/",
    redirect: "/galleries",
  },
  {
    path: "/galleries",
    component: ListGalleries,
    name: "ListGalleries",
  },
];

export const router = createRouter({
  history: routerHistory,
  strict: true,
  routes,
});
