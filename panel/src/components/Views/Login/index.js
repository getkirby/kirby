import LoginAlert from "./LoginAlert.vue";
import LoginBackButton from "./LoginBackButton.vue";
import LoginCodeForm from "./LoginCodeForm.vue";
import LoginEmailPasswordForm from "./LoginEmailPasswordForm.vue";
import LoginView from "./LoginView.vue";

export default {
	install(app) {
		app.component("k-login-alert", LoginAlert);
		app.component("k-login-back-button", LoginBackButton);
		app.component("k-login-code-form", LoginCodeForm);
		app.component("k-login-email-password-form", LoginEmailPasswordForm);
		app.component("k-login-view", LoginView);
	}
};
