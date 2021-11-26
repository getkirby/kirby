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
    Fiber.init(this.state, {
      headers: () => {
        return {
          "X-CSRF": this.state.$system.csrf
        };
      },
      /**
       * Is being called when a Fiber request
       * ends. It stops the loader unless
       * there are still running API requests
       */
      onFinish: () => {
        if (this.$api.requests.length === 0) {
          this.$store.dispatch("isLoading", false);
        }
      },
      /**
       * Is being called when a Fiber request
       * starts. The silent option is used to
       * enable/disable the loader
       *
       * @param {object} options
       */
      onStart: ({ silent }) => {
        // show the loader unless the silent option is activated
        // this is useful i.e. for background reloads (see our locking checks)
        if (silent !== true) {
          this.$store.dispatch("isLoading", true);
        }
      },
      /**
       * Loads the correct view component
       * and replaces the current state
       * on every request
       *
       * @param {object} state
       * @param {object} options
       */
      onSwap: async (state, options) => {
        options = {
          navigate: true,
          replace: false,
          ...options
        };

        this.setGlobals(state);
        this.setTitle(state);
        this.setTranslation(state);

        this.component = state.$view.component;
        this.state = state;
        this.key = options.replace === true ? this.key : state.$view.timestamp;

        if (options.navigate === true) {
          this.navigate();
        }
      },
      query: () => {
        return {
          language: this.state.$language?.code
        };
      }
    });
  },
  methods: {
    /**
     * Closes all dialogs and clears a potentially
     * blocked overflow style
     */
    navigate() {
      this.$store.dispatch("navigate");
    },

    /**
     * Registers all globals from the state in
     * the Vue prototype and the window.panel object
     *
     * @param {object} state
     */
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

    /**
     * Sets the document title on each request
     *
     * @param {object} state
     */
    setTitle(state) {
      // set the document title according to $view.title
      if (state.$view.title) {
        document.title = state.$view.title + " | " + state.$system.title;
      } else {
        document.title = state.$system.title;
      }
    },

    /**
     * Sets the lang attribute on every request
     *
     * @param {object} state
     */
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
};
