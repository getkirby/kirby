import type Api from ".";

export default (api: Api) => ({
	async create(values: Record<string, unknown>) {
		return api.post("languages", values);
	},
	async delete(code: string) {
		return api.delete("languages/" + code);
	},
	async get(code: string) {
		return api.get("languages/" + code);
	},
	async list() {
		return api.get("languages");
	},
	async update(code: string, values: Record<string, unknown>) {
		return api.patch("languages/" + code, values);
	}
});
