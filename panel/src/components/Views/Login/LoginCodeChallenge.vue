<template>
	<form class="k-login-form k-login-code-challenge" @submit.prevent="onSubmit">
		<k-user-info v-if="pending.email" :user="pending.email" />

		<k-text-field
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="$t('login.code.label.' + (isResetForm ? method : 'login'))"
			:placeholder="$t('login.code.placeholder.' + pending.challenge)"
			:required="true"
			:value="code"
			autocomplete="one-time-code"
			icon="unlock"
			name="code"
			@input="code = $event"
		/>

		<footer class="k-login-buttons">
			<k-button
				:text="$t('back')"
				icon="angle-left"
				link="/logout"
				size="lg"
				variant="filled"
				class="k-login-button k-login-back-button"
			/>

			<k-button
				:disabled="isProcessing"
				:icon="isProcessing ? 'loader' : 'check'"
				:text="isResetForm ? $t('login.reset') : $t('login')"
				size="lg"
				type="submit"
				theme="positive"
				variant="filled"
				class="k-login-button"
			/>
		</footer>
	</form>
</template>

<script>
import { props as LoginProps } from "./LoginView.vue";

export default {
	mixins: [LoginProps],
	emits: ["error"],
	data() {
		return {
			code: this.value.code ?? "",
			isProcessing: false
		};
	},
	computed: {
		isResetForm() {
			return this.method === "password-reset";
		}
	},
	methods: {
		async onSubmit() {
			this.$emit("error", null);
			this.isProcessing = true;

			try {
				await this.$api.auth.verifyCode(this.code);

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});

				if (this.method === "password-reset") {
					this.$go("reset-password");
				} else {
					this.$panel.reload();
				}
			} catch (error) {
				this.$emit("error", error);
			} finally {
				this.isProcessing = false;
			}
		}
	}
};
</script>

<style>
.k-login-code-challenge .k-user-info {
	margin-bottom: var(--spacing-6);
}
</style>
