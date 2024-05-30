import LoginView from "./LoginView.vue";

import LoginForm from "./LoginForm.vue";
import LoginAlert from "./LoginAlert.vue";
import LoginCodeForm from "./LoginCodeForm.vue";

import InstallationView from "./InstallationView.vue";
import ResetPasswordView from "./ResetPasswordView.vue";

import UserInfo from "./UserInfo.vue";

export default {
	install(app) {
		app.component("k-login-view", LoginView);
		app.component("k-login-form", LoginForm);
		app.component("k-login-code-form", LoginCodeForm);
		app.component("k-login-alert", LoginAlert);

		app.component("k-installation-view", InstallationView);
		app.component("k-reset-password-view", ResetPasswordView);

		app.component("k-user-info", UserInfo);

		/** deprecated */
		app.component("k-login", LoginForm);
		app.component("k-login-code", LoginCodeForm);
	}
};
