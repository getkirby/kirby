import { type App, reactive } from "vue";
import Activation from "./activation";
import Api from "@/api/index.js";
import Content from "./content";
import Dialog, { type DialogState } from "./dialog";
import Drag from "./drag";
import Drawer, { type DrawerState } from "./drawer";
import Dropdown, { type DropdownState } from "./dropdown.js";
import Events from "./events";
import html from "./html";
import Language, { type LanguageState } from "./language";
import { type Listener } from "./listeners";
import Menu, { type MenuState } from "./menu";
import Notification, { type NotificationState } from "./notification";
import Observers from "./observers";
import Plugins, { type PanelPlugins } from "./plugins";
import Search, { type SearchType } from "./search";
import State from "./state";
import System, { type SystemState } from "./system";
import Theme from "./theme";
import Translation, { type TranslationState } from "./translation";
import Upload from "./upload";
import User, { type UserState } from "./user";
import View, { type ViewState } from "./view";
import { type PanelRequestOptions, redirect, request } from "./request";
import { isAbortError } from "@/helpers/error";
import { isObject, length } from "@/helpers/object";
import { isEmpty } from "@/helpers/string";
import { buildUrl, isUrl } from "@/helpers/url";
import OfflineError from "@/errors/OfflineError";
import RedirectError from "@/errors/RedirectError";

type Config = {
	api: { methodOverride: boolean };
	debug: boolean;
	kirbytext: boolean;
	theme: string;
	translation: string;
	upload: number;
};

type Languages = Record<string, LanguageState>[];
type Permissions = Record<string, Record<string, boolean>>;
type Searches = Record<string, SearchType>;
type Urls = { api: string; panel: string; site: string };

export type PanelState = {
	config: Config;
	dialog: DialogState;
	drawer: DrawerState;
	dropdown: DropdownState;
	language: LanguageState;
	languages: Languages;
	license: string;
	menu: MenuState;
	multilang: boolean;
	notification: NotificationState;
	permissions: Permissions;
	searches: Searches;
	system: SystemState;
	translation: TranslationState;
	urls: Urls;
	user: UserState;
	view: ViewState;
};

/**
 * Globals are just reactive objects
 * from the backend that don't have their
 * own state objects.
 */
export const globals = [
	"config",
	"languages",
	"license",
	"multilang",
	"permissions",
	"searches",
	"urls"
] as const satisfies (keyof PanelState)[];

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
] as const satisfies (keyof PanelState)[];

/**
 * Modals are features that
 * can be opened and closed based
 * on the response
 */
export const modals = ["dialog", "drawer"] as const;
export type ModalKey = (typeof modals)[number];

export type PanelOptions = Partial<{
	[K in keyof Omit<PanelState, ModalKey>]: Partial<PanelState[K]>;
}> & {
	dialog?: (DialogState & { redirect?: string }) | null | false;
	drawer?: (DrawerState & { redirect?: string }) | null | false;
};

/**
 * The global panel object serves as a singleton
 * to access any functionality the panel offers
 * and handles the reactive, global state of the panel.
 *
 * Always initialize via Panel.create() to ensure
 * full reactivity of the Panel object.
 *
 * @since 4.0.0
 */
export default class Panel {
	app: App;

	isLoading: boolean;
	isOffline: boolean;

	// globals
	config: Config = {
		api: { methodOverride: false },
		debug: false,
		kirbytext: true,
		theme: "system",
		translation: "en",
		upload: 0
	};
	languages: Languages = [];
	license: string = "missing";
	multilang: boolean = false;
	permissions: Permissions = {};
	searches: Searches = {};
	urls: Urls = { api: "/", panel: "/", site: "/" };

	// modules
	activation: ReturnType<typeof Activation>;
	api: Api;
	content: ReturnType<typeof Content>;
	drag: ReturnType<typeof Drag>;
	drawer: ReturnType<typeof Drawer>;
	dropdown: ReturnType<typeof Dropdown>;
	dialog: ReturnType<typeof Dialog>;
	events: ReturnType<typeof Events>;
	language: ReturnType<typeof Language>;
	menu: ReturnType<typeof Menu>;
	notification: ReturnType<typeof Notification>;
	observers: ReturnType<typeof Observers>;
	plugins: ReturnType<typeof Plugins>;
	searcher: ReturnType<typeof Search>;
	system: ReturnType<typeof System>;
	theme: ReturnType<typeof Theme>;
	translation: ReturnType<typeof Translation>;
	upload: ReturnType<typeof Upload>;
	user: ReturnType<typeof User>;
	view: ReturnType<typeof View>;

	// shortcuts
	html: typeof html;
	redirect: typeof redirect;
	reload: ReturnType<typeof View>["reload"];
	t: ReturnType<typeof Translation>["translate"];

	// deprecated
	$t: ReturnType<typeof Translation>["translate"];

	constructor(
		app: App,
		state: Prettify<PanelOptions> = {},
		plugins: Prettify<PanelPlugins> = {}
	) {
		// Vue app instance
		this.app = app;

		// props
		this.isLoading = false;
		this.isOffline = false;

		this.activation = Activation();
		this.drag = Drag();
		this.events = Events(this);
		this.observers = Observers();
		this.searcher = Search(this);
		this.theme = Theme(this);
		this.upload = Upload(this);

		// state objects
		this.language = Language();
		this.menu = Menu(this);
		this.notification = Notification(this);
		this.system = System();
		this.translation = Translation();
		this.user = User();

		// features
		this.dropdown = Dropdown(this);
		this.view = View(this);
		this.content = Content(this);

		// modals
		this.drawer = Drawer(this);
		this.dialog = Dialog(this);

		// methods
		this.html = html;
		this.redirect = redirect;
		this.reload = this.view.reload.bind(this.view);

		// translator
		this.t = this.$t = this.translation.translate.bind(this.translation);

		// register all plugins
		this.plugins = Plugins(this.app, plugins);

		// set initial state
		this.set(state);

		// api needs the initial state
		// for the endpoint config
		this.api = new Api(this);
	}

	static create(app: App, plugins: Prettify<PanelPlugins> = {}): Panel {
		const panel = reactive(new Panel(app, window.panelState, plugins));

		// Register as the single source of truth for all Vue components
		window.panel = app.config.globalProperties.$panel = panel;

		// Bind all methods to the reactive proxy so that
		// `this` inside methods always refers to the reactive object
		const p = panel as Record<string, unknown>;

		for (const key in panel) {
			if (typeof p[key] === "function") {
				p[key] = (p[key] as (...args: unknown[]) => unknown).bind(panel);
			}
		}

		return panel;
	}

	/**
	 * Get the current editing context the user is in
	 */
	get context(): "dialog" | "drawer" | "view" {
		if (this.dialog.isOpen) {
			return "dialog";
		}

		if (this.drawer.isOpen) {
			return "drawer";
		}

		return "view";
	}

	/**
	 * Returns the debug state of the Panel
	 */
	get debug(): boolean {
		return this.config.debug === true;
	}

	/**
	 * Shortcut to trigger a deprecation warning
	 */
	deprecated(message: string): void {
		this.notification.deprecated(message);
	}

	/**
	 * Returns the reading direction based
	 * on the current interface translation
	 * This is used to set the dir attribute
	 * on the HTML element
	 */
	get direction(): string {
		return this.translation.direction;
	}

	error(error: unknown, openNotification = true): void {
		if (isAbortError(error) === true) {
			return;
		}

		if (error instanceof RedirectError) {
			window.location.href = error.url;
			return;
		}

		if (error instanceof OfflineError) {
			this.isOffline = true;
			return;
		}

		if (this.debug === true) {
			console.error(error);
		}

		if (openNotification === true) {
			this.notification.error(error);
			return;
		}
	}

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
	 * @returns Returns the parsed response data
	 */
	async get(url: string | URL, options = {}) {
		const { response } = await this.request(url, {
			method: "GET",
			...options
		});

		return response?.json ?? {};
	}

	get hasSearch(): boolean {
		return length(this.searches) > 0;
	}

	/**
	 * Opens a Panel URL and sets the state.
	 * This is the main difference to panel.get,
	 * which does not manipulate the state.
	 *
	 * @example
	 * const state = await panel.open("/some/url");
	 */
	async open(
		url: string | URL | Prettify<PanelOptions>,
		options: { on?: Record<string, Listener> } = {}
	): Promise<PanelState | undefined> {
		try {
			if (isUrl(url) === false) {
				this.set(url);
			} else {
				this.isLoading = true;
				const state = await this.get(url, options);

				// Preserve modal listeners across state-driven opens.
				// When opening a modal via a URL, the backend response triggers
				// a second open with a state object that doesn't include those
				// listeners, so we need to add them back to the state.
				if (isObject(options?.on) === true) {
					for (const modal of modals) {
						if (isObject(state?.[modal])) {
							state[modal].on = options.on;
						}
					}
				}

				this.set(state);
			}

			return this.state();
		} catch (error) {
			this.error(error);
			return;
		} finally {
			this.isLoading = false;
		}
	}

	overlays(): ModalKey[] {
		const overlays: ModalKey[] = [];

		if (this.drawer.isOpen === true) {
			overlays.push("drawer");
		}

		if (this.dialog.isOpen === true) {
			overlays.push("dialog");
		}

		return overlays;
	}

	/**
	 * Sends a POST request
	 *
	 * @example
	 * const data = await panel.post("/some/url", { title: "Test"})
	 *
	 * @returns Returns the parsed response data
	 */
	async post(url: string | URL, data = {}, options = {}) {
		const { response } = await this.request(url, {
			method: "POST",
			body: data,
			...options
		});

		return response.json;
	}

	/**
	 * Sends a Panel request to the backend with
	 * all the right headers and other options.
	 *
	 * It also makes sure to redirect requests,
	 * which cannot be handled via fetch and
	 * throws more useful errors.
	 */
	async request(url: string | URL, options: Partial<PanelRequestOptions> = {}) {
		return request(url, {
			referrer: this.view.path ?? undefined,
			csrf: this.system.csrf,
			...options
		});
	}

	/**
	 * Use one of the installed search types
	 * to search for content in the Panel
	 */
	async search(
		type?: string,
		query?: string,
		options?: { limit?: number; page?: number }
	) {
		type ??= this.view.search;

		// open the search dialog
		if (query === undefined) {
			this.searcher.open(type);
			return;
		}

		return this.searcher.query(type, query, options);
	}

	/**
	 * Creates a new state
	 */
	set(state: Prettify<PanelOptions> = {}): void {
		// Register all globals
		for (const global of globals) {
			const value = state[global] ?? this[global];

			if (typeof value === typeof this[global]) {
				this[global] = value as never;
			}
		}

		/**
		 * Register all state objects
		 */
		for (const key of states) {
			// if there's a new state for the
			// state object, call its state setter method
			if (isObject(state[key]) || Array.isArray(state[key])) {
				(this[key] as ReturnType<typeof State>).set(state[key]);
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
					this.open(state[modal].redirect);
					return;
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
	}

	/**
	 * Returns the state for all globals
	 * and features
	 *
	 * @example
	 * console.log(panel.state)
	 */
	state(): PanelState {
		const state: Record<string, unknown> = {};

		// Raw global values (config, languages, license, etc.)
		for (const key of globals) {
			state[key] = this[key];
		}

		// Serialized state from each state object
		// (language, menu, notification, etc.)
		for (const key of states) {
			state[key] = this[key].state();
		}

		// Serialized state from each modal (dialog, drawer)
		for (const key of modals) {
			state[key] = this[key].state();
		}

		// View is not part of the loops above
		state.view = this.view.state();

		return state as PanelState;
	}

	/**
	 * Returns the current title for the document
	 */
	get title(): string {
		return document.title;
	}

	/**
	 * Sets the document title
	 */
	set title(title: string | null) {
		if (isEmpty(this.system.title) === false) {
			title += " | " + this.system.title;
		}

		document.title = title ?? "";
	}

	/**
	 * Builds a full URL object based on the
	 * given path or another URL object and query data
	 */
	url = buildUrl;
}
