import Auth from "./auth.js";
import Delete from "./delete.js";
import Files from "./files.js";
import Get from "./get.js";
import Languages from "./languages.js";
import Pages from "./pages.js";
import Patch from "./patch.js";
import Post from "./post.js";
import Request from "./request";
import Roles from "./roles.js";
import System from "./system.js";
import Site from "./site.js";
import Translations from "./translations.js";
import Users from "./users.js";
import { rtrim } from "@/helpers/string";

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

		// methods
		this.get = Get(this);
		this.post = Post(this);
		this.patch = Patch(this);
		this.delete = Delete(this);

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
			return await Request(this)(path, options);
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
