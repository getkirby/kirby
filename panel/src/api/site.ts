import { ApiInterface } from "./index";

export interface ApiSiteEndpoints {
	blueprint: () => Promise<object>;
	blueprints: () => Promise<object>;
	changeTitle: (title: string) => Promise<object>;
	children: (query?: object) => Promise<object>;
	get: (query?: object) => Promise<object>;
	update: (data: object) => Promise<object>;
}

export default (api: Partial<ApiInterface>): ApiSiteEndpoints => {
	return {
		async blueprint() {
			return api.get("site/blueprint");
		},
		async blueprints() {
			return api.get("site/blueprints");
		},
		async changeTitle(title) {
			return api.patch("site/title", { title: title });
		},
		async children(query) {
			return api.post("site/children/search", query);
		},
		async get(query = { view: "panel" }) {
			return api.get("site", query);
		},
		async update(data) {
			return api.post("site", data);
		}
	};
};
