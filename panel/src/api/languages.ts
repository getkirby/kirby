import { ApiSetup } from "./index";

export interface ApiLanguagesEndpoints {
	/** Create a new language */
	create: (values: object) => Promise<object>;
	/** Delete language by language code */
	delete: (code: string) => Promise<object>;
	/** Get data for language */
	get: (code: string) => Promise<object>;
	/** Get data for all languages */
	list: () => Promise<object>;
	/** Update laguage data */
	update: (code: string, values: object) => Promise<object>;
}

export default (api: ApiSetup): ApiLanguagesEndpoints => {
	return {
		create: async (values) => api.post("languages", values),
		delete: async (code) => api.delete("languages/" + code),
		get: async (code) => api.get("languages/" + code),
		list: async () => api.get("languages"),
		update: async (code, values) => api.patch("languages/" + code, values)
	};
};
