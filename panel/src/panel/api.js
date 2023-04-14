import Auth from "@/api/auth.js";
import Delete from "@/api/delete.js";
import Files from "@/api/files.js";
import Get from "@/api/get.js";
import Languages from "@/api/languages.js";
import Pages from "@/api/pages.js";
import Patch from "@/api/patch.js";
import Post from "@/api/post.js";
import Request from "@/api/request.js";
import Roles from "@/api/roles.js";
import System from "@/api/system.js";
import Site from "@/api/site.js";
import Translations from "@/api/translations.js";
import Users from "@/api/users.js";
import { rtrim } from "@/helpers/string";

/**
 * Panel API Setup
 *
 * @todo All of this could be put directly into the api object
 * to avoid the additional configuration code. We don't ship it
 * as a stand-alone library anyway.
 *
 * @param {object} panel
 */
export default (panel) => {
	const api = {
		csrf: panel.system.csrf,
		endpoint: rtrim(panel.urls.api, "/"),
		methodOverwrite: true,
		language: panel.language.code,
		ping: null,
		requests: [],
		running: 0
	};

	// clear and restart the auth beacon
	const ping = () => {
		clearInterval(api.ping);
		api.ping = setInterval(api.auth.ping, 5 * 60 * 1000);
	};

	// setup the main request method
	api.request = async (path, options = {}, silent = false) => {
		// create a request id
		const id = path + "/" + JSON.stringify(options);

		// keep track of the request
		api.requests.push(id);

		// start the loader if it's not a silent request
		if (silent === false) {
			panel.isLoading = true;
		}

		try {
			return await Request(api)(path, options);
		} finally {
			// restart the ping
			ping();

			// remove the request from the running list
			api.requests = api.requests.filter((value) => {
				return value !== id;
			});

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
