
export default {
  install(Vue) {

    // Cypress helper
    window.__routed = [];

    Vue.prototype.$route = {};
    Vue.prototype.$router = {
      push(path) {
        window.__routed.push(path);
        alert("$router.push('" + path + "')");
      },
      options: {
        url: null
      }
    };

    Vue.component("router-view", {
      template: "<slot />"
    });

  }
};
