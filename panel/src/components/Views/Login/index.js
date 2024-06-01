import LoginAlert from "./LoginAlert.vue";
import LoginCodeForm from "./LoginCodeForm.vue";
import LoginForm from "./LoginForm.vue";
import LoginView from "./LoginView.vue";

export default {
	install(app) {
		app.component("k-login-alert", LoginAlert);
		app.component("k-login-code-form", LoginCodeForm);
		app.component("k-login-form", LoginForm);
		app.component("k-login-view", LoginView);

		/** deprecated */
		app.component("k-login", LoginForm);
		app.component("k-login-code", LoginCodeForm);
	}
};
