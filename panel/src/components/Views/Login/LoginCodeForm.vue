<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info v-if="pending.email" :user="pending.email" />

		<k-text-field
			:autofocus="true"
			:counter="false"
			:help="$t('login.code.text.' + pending.challenge)"
			:label="$t('login.code.label.' + mode)"
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
				:text="submitText"
				icon="check"
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
export const props = {
	props: {
		/**
		 * List of available login method names
		 */
		methods: {
			type: Array,
			default: () => []
		},
		/**
		 * Pending login data (user email, challenge type)
		 * @value { email: String, challenge: String }
		 */
		pending: {
			type: Object,
			default: () => ({ challenge: "email" })
		},
		/**
		 * Code value to prefill the input
		 */
		value: String
	}
};

export default {
	mixins: [props],
	emits: ["error"],
	data() {
		return {
			code: this.value ?? "",
			isLoading: false
		};
	},
	computed: {
		mode() {
			return this.methods.includes("password-reset")
				? "password-reset"
				: "login";
		},
		submitText() {
			const suffix = this.isLoading ? " …" : "";

			if (this.mode === "password-reset") {
				return this.$t("login.reset") + suffix;
			}

			return this.$t("login") + suffix;
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

				if (this.mode === "password-reset") {
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

<style>
.k-login-code-form .k-user-info {
	margin-bottom: var(--spacing-6);
}
</style>
