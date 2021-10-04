
import Fiber from "./index";
import Vue from "vue";

export default {
  name: "Fiber",
  data() {
    return {
      component: null,
      state: window.fiber,
      key: null
    };
  },
  created() {
    Fiber.init({
      state: this.state,
      csrf: window.fiber.$system.csrf,
      finish: () => {
        if (this.$api.requests.length === 0) {
          this.$store.dispatch("isLoading", false);
        }
      },
      start: ({ silent }) => {
        if (silent !== true) {
          this.$store.dispatch("isLoading", true);
        }
      },
      swap: async (state, options) => {
        options = {
          navigate: true,
          replace: false,
          ...options
        };

        this.setGlobals(state);
        this.setTitle(state);
        this.setTranslation(state);

        this.component = state.$view.component;
        this.state     = state;
        this.key       = options.replace === true ? this.key : state.$view.timestamp;

        if (options.navigate === true) {
          this.navigate();
        }
      }
    });
  },
  methods: {
    navigate() {
      document.documentElement.style.overflow = "visible";
      this.$store.dispatch("navigate");
    },
    setGlobals(state) {
      [
        "$config",
        "$direction",
        "$language",
        "$languages",
        "$license",
        "$menu",
        "$multilang",
        "$permissions",
        "$searches",
        "$system",
        "$translation",
        "$urls",
        "$user",
        "$view"
      ].forEach((key) => {
        if (state[key] !== undefined) {
          Vue.prototype[key] = window.panel[key] = state[key];
        } else {
          Vue.prototype[key] = state[key] = window.panel[key];
        }
      });
    },
    setTitle(state) {
      // set the document title according to $view.title
      if (state.$view.title) {
        document.title = state.$view.title + " | " + state.$system.title;
      } else {
        document.title = state.$system.title;
      }
    },
    setTranslation(state) {
      // set the lang attribute according to the current translation
      if (state.$translation) {
        document.documentElement.lang = state.$translation.code;
      }
    }
  },
  render(h) {
    if (this.component) {
      return h(this.component, {
        key: this.key,
        props: this.state.$view.props
      });
    }
  }
}
