import en from "../i18n/en.js";

export default {
  install(Vue) {

    /** Fake translations */
    Vue.prototype.$t = function(string, fallback) {
      return en[string] || fallback || "$t('" + string + "')";
    };

    /** Fake store */
    Vue.prototype.$store = {
      state: {

      },
      dispatch() {

      },
      commit() {

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

  }
};
