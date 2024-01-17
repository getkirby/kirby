/* Form */
import LoginCodeForm from "./LoginCodeForm.vue";
import LoginEmailForm from "./LoginEmailForm.vue";
import LoginPasswordForm from "./LoginPasswordForm.vue";

/** Login Elements */
import LoginAlert from "./Elements/LoginAlert.vue";
import LoginButton from "./Elements/LoginButton.vue";

export default {
	install(app) {
		app.component("k-login-alert", LoginAlert);
		app.component("k-login-button", LoginButton);

		app.component("k-login-code-form", LoginCodeForm);
		app.component("k-login-email-form", LoginEmailForm);
		app.component("k-login-password-form", LoginPasswordForm);

		/**
		 * @deprecated Use k-login-code-form instead
		 */
		app.component("k-login-code", LoginCodeForm);
		/**
		 * @deprecated Use k-login-password-form instead
		 */
		app.component("k-login", LoginPasswordForm);
	}
};
