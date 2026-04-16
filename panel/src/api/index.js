import Auth from "./auth.js";
import Files from "./files.js";
import Languages from "./languages.js";
import Pages from "./pages.js";
import Roles from "./roles.js";
import System from "./system.js";
import Site from "./site.js";
import Translations from "./translations.js";
import Users from "./users.js";

import { request } from "@/panel/request";
import { ltrim, rtrim } from "@/helpers/string";
import { buildQuery } from "@/helpers/url";

/**
 * Panel API Setup
 */
export default class Api {
	requests = [];

	constructor(panel) {
		this.panel = panel;
		this.csrf = panel.system.csrf;
		this.endpoint = rtrim(panel.urls.api, "/");
		this.methodOverride = panel.config.api?.methodOverride ?? false;
		this.language = panel.language.code;

		// modules
		this.auth = Auth(this);
		this.files = Files(this);
		this.languages = Languages(this);
		this.pages = Pages(this);
		this.roles = Roles(this);
		this.system = System(this);
		this.site = Site(this);
		this.translations = Translations(this);
		this.users = Users(this);

		// regularly ping API to keep session alive
		this.ping();
	}

	/**
	 * Sends DELETE request
	 */
	async delete(path, data, options, silent = false) {
		return this.post(path, data, options, "DELETE", silent);
	}

	/**
	 * Sends GET request
	 */
	async get(path, query, options, silent = false) {
		if (query) {
			const search = buildQuery(query).toString();

			if (search) {
				path += "?" + search;
			}
		}

		return this.request(path, { ...options, method: "GET" }, silent);
	}

	/**
	 * Sends PATCH request
	 */
	async patch(path, data, options, silent = false) {
		return this.post(path, data, options, "PATCH", silent);
	}

	/**
	 * Clear and restart the auth beacon
	 */
	ping() {
		clearInterval(this.pingId);

		this.pingId = setInterval(
			() => {
				if (this.panel.isOffline === false) {
					this.auth.ping();
				}
			},
			5 * 60 * 1000
		);
	}

	/**
	 * Sends POST request
	 */
	async post(path, data, options, method = "POST", silent = false) {
		return this.request(
			path,
			{ ...options, method: method, body: JSON.stringify(data) },
			silent
		);
	}

	async request(path, options = {}, silent = false) {
		// create a request id
		const id = path + "/" + JSON.stringify(options);

		// keep track of the request
		this.requests.push(id);

		// start the loader if it's not a silent request
		if (silent === false && options.silent !== true) {
			this.panel.isLoading = true;
		}

		// always update the language on each request to ensure
		// that only the most current one is used
		this.language = this.panel.language.code;

		try {
			const method = options.method ?? "GET";
			const url = rtrim(api.endpoint, "/") + "/" + ltrim(path, "/");

			// Rewrite non-GET/POST methods as POST when method override is enabled
			const overrideMethod =
				api.methodOverride && method !== "GET" && method !== "POST";

			const { response } = await request(url, {
				...options,
				csrf: api.csrf,
				method: overrideMethod ? "POST" : method,
				headers: {
					"x-language": api.language,
					...(overrideMethod ? { "x-http-method-override": method } : {}),
					...(options.headers ?? {})
				}
			});

			const data = response.json;

			// simplify the response
			if (data.data && data.type === "model") {
				return data.data;
			}

			return data;
		} finally {
			// restart the ping
			this.ping();

			// remove the request from the running list
			this.requests = this.requests.filter((value) => value !== id);

			// stop the loader if all requests ended
			if (this.requests.length === 0) {
				this.panel.isLoading = false;
			}
		}
	}
}
