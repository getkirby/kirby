import { ApiInterface } from "./index";

export interface ApiTranslationsEndpoints {
	/** Get all languages */
	list: () => Promise<object>;
	/** Get data for language */
	get: (locale: string) => Promise<object>;
}

export default (api: Partial<ApiInterface>): ApiTranslationsEndpoints => {
	return {
		async list() {
			return api.get("translations");
		},
		async get(locale) {
			return api.get("translations/" + locale);
		}
	};
};
