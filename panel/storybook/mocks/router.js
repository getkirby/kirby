
export default {
  install(Vue) {

    /** Fake routing */
    Vue.prototype.$route = {};
    Vue.prototype.$router = {
      push(path) {
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
