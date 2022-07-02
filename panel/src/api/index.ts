import auth, { ApiAuthEndpoints } from "./auth";
import files, { ApiFilesEndpoints } from "./files";
import languages, { ApiLanguagesEndpoints } from "./languages";
import pages, { ApiPagesEndpoints } from "./pages";
import request, { ApiRequest } from "./request";
import roles, { ApiRolesEndpoints } from "./roles";
import site, { ApiSiteEndpoints } from "./site";
import system, { ApiSystemEndpoints } from "./system";
import translations, { ApiTranslationsEndpoints } from "./translations";
import users, { ApiUsersEndpoints } from "./users";

/**
 * Interfaces
 */

export interface ApiConfig {
	endpoint: string;
	methodOverwrite: boolean;
	onPrepare: (options: object) => object;
	onStart: (id: string, silent: boolean) => void;
	onComplete: (id: string) => void;
	onSuccess: (json: object) => void;
	onParserError: ({ html, silent }) => void;
	onError: (error: Error) => void;
}

interface ApiEndpoints {
	auth: ApiAuthEndpoints;
	files: ApiFilesEndpoints;
	languages: ApiLanguagesEndpoints;
	pages: ApiPagesEndpoints;
	roles: ApiRolesEndpoints;
	site: ApiSiteEndpoints;
	system: ApiSystemEndpoints;
	translations: ApiTranslationsEndpoints;
	users: ApiUsersEndpoints;
}

interface ApiExtensions {
	config?: Partial<ApiConfig>;
	[key: string]: any;
}

export interface ApiInterface
	extends ApiConfig,
		ApiRequest,
		ApiEndpoints,
		ApiExtensions {}

/**
 * Setup
 */

export default (extensions: ApiExtensions = {}): ApiInterface => {
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

	const config: ApiConfig = {
		...defaults,
		...(extensions.config || {})
	};

	const api: Partial<ApiInterface> = {
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

	return api as ApiInterface;
};
