<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info :user="pending.email" />

		<k-text-field
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="$t('login.code.label.' + mode)"
			:novalidate="true"
			:placeholder="$t('login.code.placeholder.' + pending.challenge)"
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
				icon="angle-left"
				size="lg"
				variant="filled"
				@click="back"
			>
				{{ $t("back") }} <template v-if="isLoadingBack"> … </template>
			</k-button>

			<k-button
				class="k-login-button"
				icon="check"
				size="lg"
				type="submit"
				theme="positive"
				variant="filled"
			>
				{{ $t("login" + (mode === "password-reset" ? ".reset" : "")) }}
				<template v-if="isLoadingLogin"> … </template>
			</k-button>
		</div>
	</form>
</template>

<script>
export default {
	props: {
		methods: Array,
		pending: Object
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
		mode() {
			if (this.methods.includes("password-reset") === true) {
				return "password-reset";
			}

			return "login";
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
