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
		this.$fiber.init(this.state, {
			base: document.querySelector("base").href,
			/**
			 * Returns all custom headers for
			 * each Fiber request
			 * @returns {Object}
			 */
			headers: () => {
				return {
					"X-CSRF": this.$panel.system.csrf
				};
			},
			/**
			 * Handles fatal JSON parsing issues
			 * that cannot be converted to a valid
			 * Fiber request.
			 *
			 * @param {object}
			 */
			onFatal({ text }) {
				window.panel.notification.fatal(text);
			},
			/**
			 * Is being called when a Fiber request
			 * ends. It stops the loader unless
			 * there are still running API requests
			 */
			onFinish: () => {
				if (this.$api.requests.length === 0) {
					this.$panel.isLoading = false;
				}
			},
			/**
			 * Is being called when a new state is pushed
			 *
			 * @param {Object} state
			 */
			onPushState: (state) => {
				window.history.pushState(state, "", state.$url);
			},
			/**
			 * Is being called when a the current state is replaced
			 *
			 * @param {Object} state
			 */
			onReplaceState: (state) => {
				window.history.replaceState(state, "", state.$url);
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
					this.$panel.isLoading = true;
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
				this.setLanguage(state);
				this.setLanguages(state);
				this.setMultilang(state);
				this.setSystem(state);
				this.setTitle(state);
				this.setTranslation(state);
				this.setUrls(state);
				this.setUser(state);

				this.component = state.$view.component;
				this.state = state;
				this.key = options.replace === true ? this.key : state.$view.timestamp;

				if (options.navigate === true) {
					this.navigate();
				}
			},
			/**
			 * Returns global query parameters
			 * that should be added to all requests
			 *
			 * @returns {Object}
			 */
			query: () => {
				return {
					language: this.$panel.language.code
				};
			}
		});

		// back button event
		window.addEventListener("popstate", this.$reload);
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
		 * Temporarily connects the old fiber code with the new language module
		 *
		 * @param {object} state
		 */
		setLanguage(state) {
			if (state.$language === null) {
				this.$panel.language.reset();
			} else if (state.$language) {
				this.$panel.language.set(state.$language);
			}
		},

		/**
		 * Temporarily connects the old fiber code with the new panel
		 *
		 * @param {object} state
		 */
		setLanguages(state) {
			if (state.$languages) {
				this.$panel.languages = state.$languages;
			}
		},

		/**
		 * Temporarily connects the old fiber code with the new panel
		 *
		 * @param {object} state
		 */
		setMultilang(state) {
			this.$panel.multilang = state.$multilang;
		},

		/**
		 * Temporary state setter for the new panel.system module
		 *
		 * @param {object} state
		 */
		setSystem(state) {
			if (state.$system) {
				this.$panel.system.set(state.$system);
			}
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
		 * Sets the translation state
		 *
		 * @param {object} state
		 */
		setTranslation(state) {
			if (state.$translation) {
				this.$panel.translation.set(state.$translation);
			}
		},

		/**
		 * Temporarily transfer urls to the new panel
		 *
		 * @param {object} state
		 */
		setUrls(state) {
			if (state.$urls) {
				this.$panel.urls = state.$urls;
      }
    },
        
    /**
     * Temporary state setter for the new user module
		 *
		 * @param {object} state
		 */
		setUser(state) {
			if (state.$user) {
				this.$panel.user.set(state.$user);
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
