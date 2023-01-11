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
					"X-CSRF": this.state.$system.csrf
				};
			},
			/**
			 * Handles fatal JSON parsing issues
			 * that cannot be converted to a valid
			 * Fiber request.
			 *
			 * @param {object}
			 */
			onFatal({ text, options }) {
				this.$store.dispatch("fatal", {
					html: text,
					silent: options.silent
				});
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
					this.$store.dispatch("isLoading", true);
				}

				this.saveScrollPosition(this.state.$view);
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

				this.restoreScrollPosition(state.$view);
			},
			/**
			 * Returns global query parameters
			 * that should be added to all requests
			 *
			 * @returns {Object}
			 */
			query: () => {
				return {
					language: this.state.$language?.code
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
		},

		saveScrollPosition(view) {
			let scrollPosition = {
				height: document.body.scrollHeight,
				scrollY: window.scrollY
			}
			sessionStorage.setItem('kirby$scrollPosition$' + view.path, JSON.stringify(scrollPosition));
			console.log('saveScrollPosition', view.path, window.scrollY);
		},
	
		restoreScrollPosition(view) {
			let scrollPosition = sessionStorage.getItem('kirby$scrollPosition$' + view.path) || null;
			scrollPosition = JSON.parse(scrollPosition);
			if (scrollPosition && scrollPosition.scrollY > 0) {
				document.body.style.minHeight = scrollPosition.height + 'px';
				window.scrollTo(0, scrollPosition.scrollY);

				// TODO: This is quite hacky but it works for now
				setTimeout(() => {
					document.body.style.minHeight = 'auto';
				}, 1000);
			}
			else {
				window.scrollTo(0, 0);
			}
			console.log('restoreScrollPosition', view.path, scrollPosition);
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
