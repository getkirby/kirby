import en from "../i18n/en.js";

export default {
  install(Vue) {

    /** Fake translations */
    Vue.prototype.$t = function(string, replace = {}) {

      let message = en[string] || "$t('" + string + "')";

      Object.keys(replace).forEach(key => {
        const regex = new RegExp("{" + key + "}", "g");
        message = message.replace(regex, replace[key]);
      });

      return message;
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
