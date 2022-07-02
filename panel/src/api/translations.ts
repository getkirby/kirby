import { ApiSetup } from "./index";

export interface ApiTranslationsEndpoints {
	/** Get all languages */
	list: () => Promise<object>;
	/** Get data for language */
	get: (locale: string) => Promise<object>;
}

export default (api: ApiSetup): ApiTranslationsEndpoints => {
	return {
		async list() {
			return api.get("translations");
		},
		async get(locale) {
			return api.get("translations/" + locale);
		}
	};
};
