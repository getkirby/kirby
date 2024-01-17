<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info :user="email" />

		<k-text-field
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + challenge)"
			:label="$t('login.code.label.' + mode)"
			:novalidate="true"
			:placeholder="$t('login.code.placeholder.' + challenge)"
			:required="true"
			:value="code"
			autocomplete="one-time-code"
			icon="unlock"
			name="code"
			@input="code = $event"
		/>

		<div class="k-login-buttons">
			<k-button
				class="k-login-button k-login-back-button"
				:icon="isLoadingBack ? 'loader' : 'angle-left'"
				:text="$t('back')"
				size="lg"
				variant="filled"
				@click="back"
			/>
			<k-login-button :loading="isLoadingLogin" :text="buttonText" />
		</div>
	</form>
</template>

<script>
export default {
	props: {
		challenge: {
			default: "email",
			type: String
		},
		email: String,
		mode: {
			default: "login",
			type: String
		}
	},
	emits: ["error"],
	data() {
		return {
			code: "",
			isLoadingBack: false,
			isLoadingLogin: false
		};
	},
	computed: {
		buttonText() {
			return this.$t(
				"login" + (this.mode === "password-reset" ? ".reset" : "")
			);
		}
	},
	methods: {
		async back() {
			this.isLoadingBack = true;
			this.$go("/logout");
		},
		async login() {
			this.$emit("error", null);
			this.isLoadingLogin = true;

			try {
				await this.$api.auth.verifyCode(this.code);

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});

				if (this.mode === "password-reset") {
					this.$go("reset-password");
				} else {
					this.$reload();
				}
			} catch (error) {
				this.$emit("error", error);
			} finally {
				this.isLoadingLogin = false;
			}
		}
	}
};
</script>

<style>
.k-login-code-form .k-user-info {
	margin-bottom: var(--spacing-6);
}
</style>
