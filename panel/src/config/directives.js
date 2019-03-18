import tab from "@/directives/tab.js";

export default {
  install(Vue) {
    // tab directive
    Vue.directive("tab", tab);
  }
};
