import Activation from "./activiation.js";
import Api from "@/api/index.js";
import Content from "./content.js";
import Dialog from "./dialog.js";
import Drag from "./drag.js";
import Drawer from "./drawer.js";
import Dropdown from "./dropdown.js";
import Events from "./events.js";
import Notification from "./notification.js";
import Language from "./language.js";
import Plugins from "./plugins.js";
import Menu from "./menu.js";
import Search from "./search.js";
import System from "./system.js";
import Theme from "./theme.js";
import Translation from "./translation.js";
import { buildUrl, isUrl } from "@/helpers/url.js";
import { reactive } from "vue";
import { redirect, request } from "./request.js";
import Upload from "./upload.js";
import User from "./user.js";
import View from "./view.js";
import { isObject, length } from "@/helpers/object.js";
import { isEmpty } from "@/helpers/string.js";

/**
 * Globals are just reactive objects
 * from the backend that don't have their
 * own state objects.
 */
export const globals = {
	config: {},
	languages: [],
	license: "missing",
	multilang: false,
	permissions: {},
	searches: {},
	urls: {}
};

/**
 * Modals are features that
 * can be opened and closed based
 * on the response
 */
export const modals = ["dialog", "drawer"];

/**
 * State objects are more advanced parts
 * of the overall panel state that
 * have their own logic and methods
 */
export const states = [
	"dropdown",
	"language",
	"menu",
	"notification",
	"system",
	"translation",
	"user"
];

/**
 * The global panel object serves as a singleton
 * to access any functionality the panel offers
 * and handles the reactive, global state of the panel.
 * @since 4.0.0
 */
export default {
	create(app, plugins = {}) {
		// Vue instance
		this.app = app;

		// props
		this.isLoading = false;
		this.isOffline = false;

		this.activation = Activation(this);
		this.drag = Drag(this);
		this.events = Events(this);
		this.searcher = Search(this);
		this.theme = Theme(this);
		this.upload = Upload(this);

		// state objects
		this.language = Language(this);
		this.menu = Menu(this);
		this.notification = Notification(this);
		this.system = System(this);
		this.translation = Translation(this);
		this.user = User(this);

		// features
		this.dropdown = Dropdown(this);
		this.view = View(this);
		this.content = Content(this);

		// modals
		this.drawer = Drawer(this);
		this.dialog = Dialog(this);

		// methods
		this.redirect = redirect;
		this.reload = this.view.reload.bind(this.view);

		// translator
		this.t = this.translation.translate.bind(this.translation);

		// register all plugins
		this.plugins = Plugins(this.app, plugins);

		// set initial state
		this.set(window.panelState);

		// api needs the initial state
		// for the endpoint config
		this.api = Api(this);

		// Turn the entire panel object
		// reactive. This will only be applied
		// to objects and arrays. Methods won't be touched.
		const panel = reactive(this);

		// register the single source of truth
		// for all Vue components
		this.app.config.globalProperties.$panel = panel;

		return panel;
	},

	/**
	 * Get the current editing context
	 * the user is in.
	 */
	get context() {
		if (this.dialog.isOpen) {
			return "dialog";
		}

		if (this.drawer.isOpen) {
			return "drawer";
		}

		return "view";
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
	 * Shortcut to trigger a deprecation warning
	 *
	 * @param {String} message
	 */
	deprecated(message) {
		this.notification.deprecated(message);
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
	 *
	 * @param {Event} error
	 * @param {Boolean} openNotification
	 */
	error(error, openNotification = true) {
		if (this.debug === true) {
			console.error(error);
		}

		if (openNotification === true) {
			return this.notification.error(error);
		}
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
		const { response } = await this.request(url, {
			method: "GET",
			...options
		});

		return response?.json ?? {};
	},

	get hasSearch() {
		return length(this.searches) > 0;
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
				const state = await this.get(url, options);
				this.set(state);
				this.isLoading = false;
			}

			return this.state();
		} catch (error) {
			return this.error(error);
		}
	},

	overlays() {
		const overlays = [];

		if (this.drawer.isOpen === true) {
			overlays.push("drawer");
		}

		if (this.dialog.isOpen === true) {
			overlays.push("dialog");
		}

		return overlays;
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
		const { response } = await this.request(url, {
			method: "POST",
			body: data,
			...options
		});

		return response.json;
	},

	/**
	 * Sends a Panel request to the backend with
	 * all the right headers and other options.
	 *
	 * It also makes sure to redirect requests,
	 * which cannot be handled via fetch and
	 * throws more useful errors.
	 *
	 * @param {String} url
	 * @param {Object} options
	 * @returns {Object|false} {request, response}
	 */
	async request(url, options = {}) {
		return request(url, {
			referrer: this.view.path,
			csrf: this.system.csrf,
			...options
		});
	},

	/**
	 * Use one of the installed search types
	 * to search for content in the Panel
	 *
	 * @param {String} type
	 * @param {Object} query
	 * @param {Object} options { limit, page }
	 * @returns {Object} { code, path, referrer, results, timestamp }
	 */
	async search(type, query, options) {
		// open the search dialog
		if (query === undefined) {
			return this.searcher.open(type);
		}

		return this.searcher.query(type, query, options);
	},

	/**
	 * Creates a new state
	 *
	 * @param {Object} state
	 */
	set(state = {}) {
		// Register all globals
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
		 * Register all state objects
		 */
		for (const key of states) {
			// if there's a new state for the
			// state object, call its state setter method
			if (isObject(state[key]) || Array.isArray(state[key])) {
				this[key].set(state[key]);
			}
		}

		/**
		 * Toggle modals
		 */
		for (const modal of modals) {
			// if there's a new state for the
			// modal, call its state setter method
			if (isObject(state[modal]) === true) {
				if (state[modal].redirect) {
					return this.open(state[modal].redirect);
				} else {
					this[modal].open(state[modal]);
				}
			}

			// modals will be closed if the response is null or false.
			// on undefined, the state of the modal stays untouched
			else if (state[modal] !== undefined) {
				// force close all nested modals
				this[modal].close(true);
			}
		}

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

		for (const key in globals) {
			state[key] = this[key] ?? globals[key];
		}

		for (const key of states) {
			state[key] = this[key].state();
		}

		for (const key of modals) {
			state[key] = this[key].state();
		}

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
			title += " | " + this.system.title;
		}

		document.title = title;
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
