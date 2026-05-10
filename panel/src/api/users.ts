import type Api from ".";

export default (api: Api) => ({
	async blueprint(id: string) {
		return api.get("users/" + id + "/blueprint");
	},
	async blueprints(id: string, section: string) {
		return api.get("users/" + id + "/blueprints", {
			section: section
		});
	},
	async changeEmail(id: string, email: string) {
		return api.patch("users/" + id + "/email", { email });
	},
	async changeLanguage(id: string, language: string) {
		return api.patch("users/" + id + "/language", { language });
	},
	async changeName(id: string, name: string) {
		return api.patch("users/" + id + "/name", { name });
	},
	async changePassword(id: string, password: string, currentPassword: string) {
		return api.patch("users/" + id + "/password", {
			password,
			currentPassword
		});
	},
	async changeRole(id: string, role: string) {
		return api.patch("users/" + id + "/role", { role });
	},
	async create(data: Record<string, unknown>) {
		return api.post("users", data);
	},
	async delete(id: string) {
		return api.delete("users/" + id);
	},
	async deleteAvatar(id: string) {
		return api.delete("users/" + id + "/avatar");
	},
	link(id: string, path: string) {
		return "/" + this.url(id, path);
	},
	async list(query?: Record<string, unknown>) {
		return api.post(this.url(null, "search"), query);
	},
	async get(id: string, query?: Record<string, unknown>) {
		return api.get("users/" + id, query);
	},
	async roles(id: string | null) {
		const roles = await api.get<{
			data: { name: string; title: string; description?: string }[];
		}>(this.url(id, "roles"));

		return roles.data.map((role) => ({
			info:
				role.description ??
				`(${window.panel.t("role.description.placeholder")})`,
			text: role.title,
			value: role.name
		}));
	},
	async search(query: Record<string, unknown>) {
		return api.post("users/search", query);
	},
	async update(id: string, data: Record<string, unknown>) {
		return api.patch("users/" + id, data);
	},
	url(id: string | null, path?: string) {
		let url = !id ? "users" : "users/" + id;

		if (path) {
			url += "/" + path;
		}

		return url;
	}
});
