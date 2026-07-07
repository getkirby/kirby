<template>
	<div class="k-login-form k-login-webauthn-method-form">
		<k-button
			:disabled="loading"
			:icon="loading ? 'loader' : 'fingerprint'"
			:text="$t('login.method.webauthn.label')"
			size="lg"
			theme="positive"
			variant="filled"
			@click="authenticate('password')"
		/>
	</div>
</template>

<script>
import LoginWebauthnForm from "./LoginWebauthnForm.vue";

/**
 * Login form for using passkeys auth
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
export default {
	extends: LoginWebauthnForm,
	async created() {
		await this.authenticate("password");
	}
};
</script>
