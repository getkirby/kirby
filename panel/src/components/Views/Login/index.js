import LoginAlert from "./LoginAlert.vue";
import LoginCodeChallenge from "./LoginCodeChallenge.vue";
import LoginPasswordMethod from "./LoginPasswordMethod.vue";
import LoginView from "./LoginView.vue";

/** deprecated */
import LoginCodeForm from "./LoginCodeForm.vue";
import LoginForm from "./LoginForm.vue";

export default {
	install(app) {
		app.component("k-login-alert", LoginAlert);
		app.component("k-login-code-challenge", LoginCodeChallenge);
		app.component("k-login-password-method", LoginPasswordMethod);
		app.component("k-login-view", LoginView);

		/** deprecated */
		app.component("k-login", LoginForm);
		app.component("k-login-form", LoginForm);
		app.component("k-login-code", LoginCodeForm);
		app.component("k-login-code-form", LoginCodeForm);
	}
};
