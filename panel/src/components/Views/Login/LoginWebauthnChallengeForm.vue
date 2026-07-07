<template>
	<form
		class="k-login-form k-login-webauthn-challenge-form"
		@submit.prevent="authenticate('code')"
	>
		<k-user-info :user="user" />

		<k-login-footer :back="true">
			<k-login-submit
				:icon="submit.icon"
				:label="submit.label"
				:loading="loading"
			/>
		</k-login-footer>
	</form>
</template>

<script>
import LoginWebauthnForm from "./LoginWebauthnForm.vue";

/**
 * Login form for using passkeys challenge (e.g. for 2FA)
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
export default {
	extends: LoginWebauthnForm,
	props: {
		submit: Object,
		user: String
	},
	async mounted() {
		await this.authenticate("code");
	}
};
</script>

<style>
.k-login-webauthn-challenge-form .k-login-submit {
	flex-grow: 1;
}
</style>
