export default (api) => ({
	async list(params) {
		return api.get("roles", params);
	},
	async get(name) {
		return api.get("roles/" + name);
	}
});
