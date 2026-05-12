import type Api from ".";

export default (api: Api) => ({
	async blueprint() {
		return api.get("site/blueprint");
	},
	async blueprints() {
		return api.get("site/blueprints");
	},
	async changeTitle(title: string) {
		return api.patch("site/title", { title });
	},
	async children(query?: Record<string, unknown>) {
		return api.post("site/children/search", query);
	},
	async get(query: Record<string, unknown> = { view: "panel" }) {
		return api.get("site", query);
	},
	async update(data: Record<string, unknown>) {
		return api.post("site", data);
	}
});
