import Auth from "./auth";
import Files from "./files";
import Languages from "./languages";
import Pages from "./pages";
import Roles from "./roles";
import System from "./system";
import Site from "./site";
import Translations from "./translations";
import Users from "./users";

import { request, type PanelRequestOptions } from "@/panel/request";
import { ltrim, rtrim } from "@/helpers/string";
import { buildQuery } from "@/helpers/url";

/**
 * Panel API Setup
 */
export default class Api {
	csrf: string;
	endpoint: string;
	methodOverride: boolean;
	language: string;
	panel: TODO;
	pingId?: ReturnType<typeof setInterval>;
	requests: string[] = [];

	auth: ReturnType<typeof Auth>;
	files: ReturnType<typeof Files>;
	languages: ReturnType<typeof Languages>;
	pages: ReturnType<typeof Pages>;
	roles: ReturnType<typeof Roles>;
	system: ReturnType<typeof System>;
	site: ReturnType<typeof Site>;
	translations: ReturnType<typeof Translations>;
	users: ReturnType<typeof Users>;

	constructor(panel: TODO) {
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
	async delete<T>(
		path: string,
		data?: Record<string, unknown>,
		options?: Record<string, unknown>,
		silent = false
	): Promise<T> {
		return this.post<T>(path, data, options, "DELETE", silent);
	}

	/**
	 * Sends GET request
	 */
	async get<T>(
		path: string,
		query?: Record<string, unknown>,
		options?: Record<string, unknown>,
		silent = false
	): Promise<T> {
		if (query) {
			const search = buildQuery(query).toString();

			if (search) {
				path += "?" + search;
			}
		}

		return this.request<T>(path, { ...options, method: "GET" }, silent);
	}

	/**
	 * Sends PATCH request
	 */
	async patch<T>(
		path: string,
		data?: Record<string, unknown>,
		options?: Record<string, unknown>,
		silent = false
	): Promise<T> {
		return this.post<T>(path, data, options, "PATCH", silent);
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
	async post<T>(
		path: string,
		data?: Record<string, unknown>,
		options?: Record<string, unknown>,
		method = "POST",
		silent = false
	): Promise<T> {
		return this.request<T>(
			path,
			{ ...options, method, body: JSON.stringify(data) },
			silent
		);
	}

	async request<T>(
		path: string,
		options: Record<string, unknown> = {},
		silent = false
	): Promise<T> {
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
			const method = (options.method as string) ?? "GET";
			const url = rtrim(this.endpoint, "/") + "/" + ltrim(path, "/");

			// Rewrite non-GET/POST methods as POST when method override is enabled
			const overrideMethod =
				this.methodOverride && method !== "GET" && method !== "POST";

			const { response } = await request(url, {
				...(options as Partial<PanelRequestOptions>),
				csrf: this.csrf,
				method: overrideMethod ? "POST" : method,
				headers: {
					"x-language": this.language,
					...(overrideMethod ? { "x-http-method-override": method } : {}),
					...(options.headers ?? {})
				}
			});

			const data = response.json;

			// simplify the response
			if (data.data && data.type === "model") {
				return data.data as T;
			}

			return data as T;
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
