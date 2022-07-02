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
	onPrepare: (options: RequestInit) => RequestInit;
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

export interface ApiSetup extends ApiConfig, ApiRequest, ApiExtensions {}

export interface ApiInterface extends ApiSetup, ApiEndpoints {}

/**
 * Setup
 */

export default (extensions: ApiExtensions = {}): ApiInterface => {
	const defaults: ApiConfig = {
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

	const setup: ApiSetup = {
		...config,
		...request(config),
		...extensions
	};

	return {
		...setup,
		auth: auth(setup),
		files: files(setup),
		languages: languages(setup),
		pages: pages(setup),
		roles: roles(setup),
		system: system(setup),
		site: site(setup),
		translations: translations(setup),
		users: users(setup)
	};
};
