import Api from "./api.js";
import Dialog from "./dialog.js";
import Drawer from "./drawer.js";
import Dropdown from "./dropdown.js";
import Events from "./events.js";
import Notification from "./notification.js";
import Language from "./language.js";
import Plugins from "./plugins.js";
import System from "./system.js";
import Translation from "./translation.js";
import { buildUrl, isUrl } from "@/helpers/url.js";
import { reactive } from "vue";
import { redirect, request } from "./request.js";
import User from "./user.js";
import View from "./view.js";
import { isObject } from "@/helpers/object.js";
import { isEmpty } from "@/helpers/string.js";

/**
 * Globals are just reactive objects
 * from the backend that don't have their
 * own modules.
 */
export const globals = {
	config: {},
	languages: [],
	license: false,
	menu: [],
	multilang: false,
	permissions: {},
	searches: {},
	urls: {}
};

/**
 * Islands are features that
 * can be opened and closed based
 * on the response
 */
export const islands = ["dialog", "drawer"];

/**
 * Modules are more advanced parts
 * of the state that have their own
 * logic and methods
 */
export const modules = [
	"dropdown",
	"language",
	"notification",
	"system",
	"translation",
	"user"
];

/**
 * The global panel object serves as a singleton
 * to access any functionality the panel offers
 * and handles the reactive, global state of the panel.
 */
export default {
	create(plugins = {}) {
		// props
		this.isLoading = false;

		// modules
		this.events = Events(this);
		this.language = Language(this);
		this.notification = Notification(this);
		this.system = System(this);
		this.translation = Translation(this);
		this.user = User(this);

		// islands
		this.drawer = Drawer(this);
		this.dialog = Dialog(this);

		// features
		this.dropdown = Dropdown(this);
		this.view = View(this);

		// methods
		this.redirect = redirect;
		this.reload = this.view.reload.bind(this.view);
		this.request = request;

		// translator
		this.t = this.translation.translate.bind(this.translation);

		// register all plugins
		this.plugins = Plugins(window.Vue, plugins);

		// set initial state
		this.set(window.fiber);

		// api needs the initial state
		// for the endpoint config
		this.api = Api(this);

		// Turn the entire panel object
		// reactive. This will only be applied
		// to objects and arrays. Methods won't be touched.
		return reactive(this);
	},

	/**
	 * Returns the debug state of the Panel
	 *
	 * @returns {Boolean}
	 */
	get debug() {
		return this.config.debug === true;
	},

	/**
	 * Returns the reading direction based
	 * on the current interface translation
	 * This is used to set the dir attribute
	 * on the HTML element
	 *
	 * @returns {String}
	 */
	get direction() {
		return this.translation.direction;
	},

	/**
	 * Sends a GET request
	 *
	 * @example
	 * const data = await panel.get("/some/url");
	 *
	 * @example
	 * const data = await panel.get("/some/url", {
	 *   query: {
	 *     search: "Foo"
	 *   }
	 * });
	 *
	 * @param {String|URL} url
	 * @param {Object} options
	 * @returns {Object} Returns the parsed response data
	 */
	async get(url, options = {}) {
		const { response } = await request(url, {
			method: "GET",
			...options
		});

		return response.json;
	},

	/**
	 * Opens a Panel URL and sets the state.
	 * This is the main difference to panel.get,
	 * which does not manipulate the state.
	 *
	 * @example
	 * const state = await panel.open("/some/url");
	 *
	 * @param {String|URL} url
	 * @param {Object} options
	 * @returns {Object} Returns the new state
	 */
	async open(url, options = {}) {
		try {
			if (isUrl(url) === false) {
				this.set(url);
			} else {
				this.isLoading = true;
				this.set(await this.get(url, options));
				this.isLoading = false;
			}

			return this.state();
		} catch (error) {
			return this.notification.error(error);
		}
	},

	/**
	 * Sends a POST request
	 *
	 * @example
	 * const data = await panel.post("/some/url", { title: "Test"})
	 *
	 * @param {String|URL} url
	 * @param {Object} data
	 * @param {Object} options
	 * @returns {Object} Returns the parsed response data
	 */
	async post(url, data = {}, options = {}) {
		const { response } = await request(url, {
			method: "POST",
			body: data,
			...options
		});

		return response.json;
	},

	/**
	 * Use one of the installed search types
	 * to search for content in the panel
	 *
	 * @param {String} type
	 * @param {Object} query
	 * @returns {Object} { code, path, referrer, results, timestamp }
	 */
	async search(type, query) {
		const { $search } = await this.get(`/search/${type}`, {
			query: { query }
		});

		return $search;
	},

	/**
	 * Creates a new state
	 *
	 * @param {Object} state
	 */
	set(state) {
		/**
		 * Old fiber requests use $ as key prefix
		 * This will remove the dollar sign in keys first
		 * @todo remove this as soon as fiber requests
		 * no longer use $ as prefix.
		 */
		state = Object.fromEntries(
			Object.entries(state).map(([k, v]) => [k.replace("$", ""), v])
		);

		/**
		 * Register all globals
		 */
		for (const global in globals) {
			// 1. check for a new state
			// 2. jump back to the previous
			// 3. take the default as last resort
			const value = state[global] ?? this[global] ?? globals[global];

			// only apply new values that match the type of the default value
			if (typeof value === typeof globals[global]) {
				this[global] = value;
			}
		}

		/**
		 * Register all modules
		 */
		modules.forEach((module) => {
			// if there's a new state for the
			// module, call its state setter method
			if (isObject(state[module])) {
				this[module].set(state[module]);
			}
		});

		/**
		 * Toggle islands
		 */
		islands.forEach((island) => {
			// if there's a new state for the
			// module, call its state setter method
			if (isObject(state[island])) {
				return this[island].open(state[island]);
			}

			// islands will be closed if the response is null or false.
			// on undefined, the state of the island stays untouched
			if (state[island] !== undefined) {
				this[island].close(state[island]);
			}
		});

		/**
		 * Toggle the dropdown
		 */
		if (isObject(state.dropdown) === true) {
			this.dropdown.open(state.dropdown);
		} else if (state.dropdown !== undefined) {
			this.dropdown.close();
		}

		/**
		 * Open the view
		 */
		if (isObject(state.view) === true) {
			this.view.open(state.view);
		}
	},

	/**
	 * Returns the state for all globals
	 * and features
	 *
	 * @example
	 * console.log(panel.state)
	 *
	 * @returns {Object}
	 */
	state() {
		const state = {};

		for (const global in globals) {
			state[global] = this[global] ?? globals[global];
		}

		modules.forEach((module) => {
			state[module] = this[module].state();
		});

		islands.forEach((island) => {
			state[island] = this[island].state();
		});

		state.dropdown = this.dropdown.state();
		state.view = this.view.state();

		return state;
	},

	/**
	 * Returns the current title for the document
	 *
	 * @returns {String}
	 */
	get title() {
		return document.title;
	},

	/**
	 * Sets the document title
	 *
	 * @param {String} title
	 */
	set title(title) {
		if (isEmpty(this.system.title) === false) {
			document.title = title + " | " + this.system.title;
		} else {
			document.title = title;
		}
	},

	/**
	 * Builds a full URL object based on the
	 * given path or another URL object and query data
	 *
	 * @param {String|URL} url
	 * @param {Object} query
	 * @param {String|URL} origin
	 * @returns {URL}
	 */
	url(url = "", query = {}, origin) {
		return buildUrl(url, query, origin);
	}
};
