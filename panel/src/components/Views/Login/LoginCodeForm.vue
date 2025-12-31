<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info v-if="pending.email" :user="pending.email" />

		<k-text-field
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="
				isResetForm
					? $t('login.code.label.password-reset')
					: $t('login.code.label.login')
			"
			:placeholder="$t('login.code.placeholder.' + pending.challenge)"
			:required="true"
			:value="code"
			autocomplete="one-time-code"
			icon="unlock"
			name="code"
			@input="code = $event"
		/>

		<footer class="k-login-buttons">
			<k-login-back-button />

			<k-button
				:disabled="isLoading"
				:icon="isLoading ? 'loader' : 'check'"
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
			isLoading: false
		};
	},
	computed: {
		isResetForm() {
			return this.methods.includes("password-reset");
		}
	},
	methods: {
		async login() {
			this.$emit("error", null);
			this.isLoading = true;

			try {
				await this.$api.auth.verifyCode(this.code);

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});

				if (this.isResetForm) {
					this.$go("reset-password");
				} else {
					this.$panel.reload();
				}
			} catch (error) {
				this.$emit("error", error);
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>
