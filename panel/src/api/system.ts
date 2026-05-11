import type Api from ".";

export default (api: Api) => ({
	async get(options: Record<string, unknown> = { view: "panel" }) {
		return api.get("system", options);
	},
	async install(user: Record<string, unknown>) {
		const auth = await api.post<Record<string, unknown>>(
			"system/install",
			user
		);
		return auth.user;
	},
	async register(license: Record<string, unknown>) {
		return api.post("system/register", license);
	}
});
