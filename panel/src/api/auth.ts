import type Api from ".";

export default (api: Api) => ({
	async login(user: { email: string; password: string; remember?: boolean }) {
		const data = {
			long: user.remember ?? false,
			email: user.email,
			password: user.password
		};

		return api.post("auth/login", data);
	},
	async logout() {
		return api.post("auth/logout");
	},
	async ping() {
		return api.post("auth/ping");
	},
	async user(params?: Record<string, unknown>) {
		return api.get("auth", params);
	},
	async verifyCode(code: string) {
		return api.post("auth/code", { code });
	}
});
