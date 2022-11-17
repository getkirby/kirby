import { reactive } from "vue";

// modules
import content from "./content.js";
import drawers from "./drawers.js";
import notification from "./notification.js";

const store = reactive({
	/**
	 * Modules
	 */
	content,
	drawers,
	notification,

	/**
	 * Root state
	 */
	state: {
		dialog: null,
		drag: null,
		fatal: false,
		isLoading: false
	},

	/**
	 * Root actions
	 */
	dialog(dialog) {
		this.state.dialog = dialog;
	},
	drag(drag) {
		this.state.drag = drag;
	},
	fatal(options) {
		// close the fatal window if false
		// is passed as options
		if (options === false) {
			this.state.fatal = false;
			return;
		}

		console.error("The JSON response could not be parsed");

		// show the full response in the console
		// if debug mode is enabled
		if (window.panel.$config.debug) {
			console.info(options.html);
		}

		// only show the fatal dialog if the silent
		// option is not set to true
		if (!options.silent) {
			this.state.fatal = options.html;
		}
	},
	isLoading(loading) {
		this.state.isLoading = loading === true;
	},
	navigate() {
		this.dialog(null);
		this.drawers.close();
	}
});

export default {
	install(app) {
		// polyfill: module's state
		store.state.content = new Proxy(store.content.state, {});
		store.state.drawers = new Proxy(store.drawers.state, {});
		store.state.notification = new Proxy(store.notification.state, {});

		// polyfill:dispatch actions
		store.dispatch = function (event, args) {
			const name = event.split("/");
			const base = store[name[0]];

			if (!name[1]) {
				return store[name[0]](args);
			}

			return base[name[1]].apply(base, args);
		};

		// polyfill: getters
		store.getters = new Proxy(
			{},
			{
				get(target, property) {
					const name = property.split("/");

					if (!name[1]) {
						return store[name[0]];
					}

					return store[name[0]][name[1]].bind(store[name[0]]);
				}
			}
		);

		// Vue shortcuts
		window.panel.$store = app.prototype.$store = store;
		window.panel.$drawers = app.prototype.$drawers = store.drawers;
		window.panel.$nofitication = app.prototype.$nofitication =
			store.nofitication;
	}
};
