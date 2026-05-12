import type Api from ".";

export default (api: Api) => ({
	async list() {
		return api.get("translations");
	},
	async get(locale: string) {
		return api.get("translations/" + locale);
	}
});
