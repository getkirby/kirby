export default {
  install(Vue) {

    /** Fake translations */
    Vue.prototype.$t = function(string) {
      return "$t('" + string + "')";
    };

    /** Fake store */
    Vue.prototype.$store = {
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
      }
    };

  }
};
