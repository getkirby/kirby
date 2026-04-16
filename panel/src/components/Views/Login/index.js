import LoginAlert from "./LoginAlert.vue";
import LoginBack from "./LoginBack.vue";
import LoginCode from "./LoginCode.vue";
import LoginFooter from "./LoginFooter.vue";
import LoginChallenges from "./LoginChallenges.vue";
import LoginMethods from "./LoginMethods.vue";
import LoginRemember from "./LoginRemember.vue";
import LoginSubmit from "./LoginSubmit.vue";
import LoginView from "./LoginView.vue";

import LoginPasswordResetMethodForm from "./LoginPasswordResetMethodForm.vue";
import LoginCodeMethodForm from "./LoginCodeMethodForm.vue";
import LoginPasswordMethodForm from "./LoginPasswordMethodForm.vue";

import LoginEmailChallengeForm from "./LoginEmailChallengeForm.vue";
import LoginTotpChallengeForm from "./LoginTotpChallengeForm.vue";

import LoginCodeForm from "./LoginCodeForm.vue";
import LoginForm from "./LoginForm.vue";

export default {
	install(app) {
		app.component("k-login-alert", LoginAlert);
		app.component("k-login-back", LoginBack);
		app.component("k-login-code", LoginCode);
		app.component("k-login-footer", LoginFooter);
		app.component("k-login-challenges", LoginChallenges);
		app.component("k-login-methods", LoginMethods);
		app.component("k-login-remember", LoginRemember);
		app.component("k-login-submit", LoginSubmit);
		app.component("k-login-view", LoginView);

		app.component("k-login-password-method-form", LoginPasswordMethodForm);
		app.component("k-login-code-method-form", LoginCodeMethodForm);
		app.component(
			"k-login-password-reset-method-form",
			LoginPasswordResetMethodForm
		);
		app.component("k-login-email-challenge-form", LoginEmailChallengeForm);
		app.component("k-login-totp-challenge-form", LoginTotpChallengeForm);

		/** deprecated */
		app.component("k-login-form", LoginForm);
		app.component("k-login-code-form", LoginCodeForm);
	}
};
