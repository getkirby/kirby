import Auth from "./auth.js";
import Delete from "./delete.js";
import Files from "./files.js";
import Get from "./get.js";
import Languages from "./languages.js";
import Pages from "./pages.js";
import Patch from "./patch.js";
import Post from "./post.js";
import Request from "./request.js";
import Roles from "./roles.js";
import System from "./system.js";
import Site from "./site.js";
import Translations from "./translations.js";
import Users from "./users.js";
import { rtrim } from "@/helpers/string";

/**
 * Panel API Setup
 *
 * @param {object} panel
 */
export default (panel) => {
	const api = {
		csrf: panel.system.csrf,
		endpoint: rtrim(panel.urls.api, "/"),
		methodOverride: panel.config.api?.methodOverride ?? false,
		ping: null,
		requests: [],
		running: 0
	};

	// clear and restart the auth beacon
	const ping = () => {
		clearInterval(api.ping);
		api.ping = setInterval(
			() => {
				if (panel.isOffline === false) {
					api.auth.ping();
				}
			},
			5 * 60 * 1000
		);
	};

	// setup the main request method
	api.request = async (path, options = {}, silent = false) => {
		// create a request id
		const id = path + "/" + JSON.stringify(options);

		// keep track of the request
		api.requests.push(id);

		// start the loader if it's not a silent request
		if (silent === false && options.silent !== true) {
			panel.isLoading = true;
		}

		// always update the language on each request to ensure
		// that only the most current one is used
		api.language = panel.language.code;

		try {
			return await Request(api)(path, options);
		} finally {
			// restart the ping
			ping();

			// remove the request from the running list
			api.requests = api.requests.filter((value) => value !== id);

			// stop the loader if all requests ended
			if (api.requests.length === 0) {
				panel.isLoading = false;
			}
		}
	};

	// modules
	api.auth = Auth(api);
	api.delete = Delete(api);
	api.files = Files(api);
	api.get = Get(api);
	api.languages = Languages(api);
	api.pages = Pages(api);
	api.patch = Patch(api);
	api.post = Post(api);
	api.roles = Roles(api);
	api.system = System(api);
	api.site = Site(api);
	api.translations = Translations(api);
	api.users = Users(api);

	// regularly ping API to keep session alive
	ping();

	return api;
};
