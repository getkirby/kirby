import auth from "./auth.js";
import files from "./files.js";
import languages from "./languages.js";
import pages from "./pages.js";
import request from "./request.js";
import roles from "./roles.js";
import system from "./system.js";
import site from "./site.js";
import translations from "./translations.js";
import users from "./users.js";

export default (extensions = {}) => {
	const defaults = {
		endpoint: "/api",
		methodOverwrite: true,
		onPrepare(options) {
			return options;
		},
		onStart() {},
		onComplete() {},
		onSuccess() {},
		onParserError() {},
		onError(error) {
			window.console.log(error.message);
			throw error;
		}
	};

	const config = {
		...defaults,
		...(extensions.config || {})
	};

	let api = {
		...config,
		...request(config),
		...extensions
	};

	api.auth = auth(api);
	api.files = files(api);
	api.languages = languages(api);
	api.pages = pages(api);
	api.roles = roles(api);
	api.system = system(api);
	api.site = site(api);
	api.translations = translations(api);
	api.users = users(api);

	return api;
};
