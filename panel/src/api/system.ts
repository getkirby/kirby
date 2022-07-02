import { ApiInterface } from "./index";

export interface ApiSystemEndpoints {
	get: (options: object) => Promise<object>;
	install: (user: object) => Promise<object>;
	register: (license: object) => Promise<object>;
}

export default (api: Partial<ApiInterface>): ApiSystemEndpoints => {
	return {
		async get(options = { view: "panel" }) {
			return api.get("system", options);
		},
		async install(user) {
			const auth = await api.post("system/install", user);
			return auth.user;
		},
		async register(license) {
			return api.post("system/register", license);
		}
	};
};
