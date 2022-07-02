import { ApiSetup } from "./index";

export interface ApiAuthEndpoints {
	/** Attempt to sign in the user */
	login: (user: {
		email: string;
		password: string;
		remember?: boolean;
	}) => Promise<object>;
	/** Sign out current user */
	logout: () => Promise<object>;
	/** Get current user data */
	user: (params: object) => Promise<object>;
	/** Verify auth code */
	verifyCode: (code: string) => Promise<object>;
}

export default (api: ApiSetup): ApiAuthEndpoints => {
	return {
		async login(user) {
			const data = {
				long: user.remember || false,
				email: user.email,
				password: user.password
			};

			return api.post("auth/login", data);
		},
		async logout() {
			return api.post("auth/logout");
		},
		async user(params) {
			return api.get("auth", params);
		},
		async verifyCode(code) {
			return api.post("auth/code", { code });
		}
	};
};
