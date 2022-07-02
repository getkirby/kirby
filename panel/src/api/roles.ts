import { ApiSetup } from "./index";

export interface ApiRolesEndpoints {
	list: (params: object) => Promise<object>;
	get: (name: string) => Promise<object>;
}

export default (api: ApiSetup): ApiRolesEndpoints => {
	return {
		async list(params) {
			return api.get("roles", params);
		},
		async get(name) {
			return api.get("roles/" + name);
		}
	};
};
