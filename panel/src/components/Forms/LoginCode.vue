<template>
	<form class="k-login-form k-login-code-form" @submit.prevent="login">
		<k-user-info v-if="pending.email" :user="pending.email" />

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

		<footer class="k-login-buttons">
			<k-button
				class="k-login-button k-login-back-button"
				icon="angle-left"
				size="lg"
				variant="filled"
				@click="$go('/logout')"
			>
				{{ $t("back") }}
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
				<template v-if="isLoading"> … </template>
			</k-button>
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
					this.$reload();
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
