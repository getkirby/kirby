<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info :user="pending.email" />

		<k-text-field
			v-model="code"
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="$t('login.code.label.' + mode)"
			:novalidate="true"
			:placeholder="$t('login.code.placeholder.' + pending.challenge)"
			:required="true"
			autocomplete="one-time-code"
			icon="unlock"
			name="code"
		/>

		<div class="k-login-buttons">
			<k-button
				class="k-login-button k-login-back-button"
				icon="angle-left"
				@click="back"
			>
				{{ $t("back") }} <template v-if="isLoadingBack"> … </template>
			</k-button>

			<k-button class="k-login-button" icon="check" type="submit">
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
				this.$store.dispatch("notification/success", this.$t("welcome"));

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
	height: 38px;
	margin-bottom: 2.25rem;
	padding: 0.5rem;
	background: var(--color-white);
	border-radius: var(--rounded-xs);
	box-shadow: var(--shadow);
}
</style>
