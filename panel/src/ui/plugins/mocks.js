import en from "../i18n/en.js";

export default {
  install(Vue) {

    /** Fake translations */
    Vue.prototype.$t = function(string, replace) {
      return en[string] || "$t('" + string + "')";
    };

    /** Fake store */
    Vue.prototype.$store = {
      state: {

      },
      dispatch(action, ...args) {
        console.log(`store.dispatch(${action}, ${args[0]})`);
      },
      commit() {
        console.log(`store.commit(${action}, ${args[0]})`);
      }
    };

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
