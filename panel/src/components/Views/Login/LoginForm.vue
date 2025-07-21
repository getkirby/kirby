<template>
	<form class="k-login-form" @submit.prevent="login">
		<div class="k-login-fields">
			<button
				v-if="canToggle === true"
				class="k-login-toggler"
				type="button"
				@click="toggle"
			>
				{{ toggleText }}
			</button>

			<k-fieldset
				ref="fieldset"
				:fields="fields"
				:value="user"
				@input="user = $event"
			/>
		</div>

		<footer class="k-login-buttons">
			<k-checkbox-input
				v-if="isResetForm === false"
				:label="$t('login.remember')"
				:checked="user.remember"
				:value="user.remember"
				@input="user.remember = $event"
			/>

			<k-button
				class="k-login-button"
				icon="check"
				size="lg"
				theme="positive"
				type="submit"
				variant="filled"
			>
				{{ submitText }}
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
		 * Values to prefill the inputs
		 * @value { email: String, password: String, remember: Boolean }
		 */
		value: {
			type: Object,
			default: () => ({})
		}
	}
};

export default {
	mixins: [props],
	emits: ["error"],
	data() {
		return {
			mode: null,
			isLoading: false,
			user: {
				email: "",
				password: "",
				remember: false,
				...this.value
			}
		};
	},
	computed: {
		alternateMode() {
			if (this.form === "email-password") {
				return "email";
			}

			return "email-password";
		},
		canToggle() {
			if (this.codeMode === null) {
				return false;
			}

			if (this.methods.includes("password") === false) {
				return false;
			}

			return (
				this.methods.includes("password-reset") === true ||
				this.methods.includes("code") === true
			);
		},
		codeMode() {
			if (this.methods.includes("password-reset") === true) {
				return "password-reset";
			}
			if (this.methods.includes("code") === true) {
				return "code";
			}
			return null;
		},
		fields() {
			const fields = {
				email: {
					autofocus: true,
					label: this.$t("email"),
					type: "email",
					required: true,
					link: false
				}
			};

			if (this.form === "email-password") {
				fields.password = {
					label: this.$t("password"),
					type: "password",
					minLength: 8,
					required: true,
					autocomplete: "current-password",
					counter: false
				};
			}

			return fields;
		},
		form() {
			if (this.mode) {
				return this.mode;
			}

			if (this.methods[0] === "password") {
				return "email-password";
			}

			return "email";
		},
		isResetForm() {
			return this.codeMode === "password-reset" && this.form === "email";
		},
		submitText() {
			const suffix = this.isLoading ? " …" : "";

			if (this.isResetForm) {
				return this.$t("login.reset") + suffix;
			}

			return this.$t("login") + suffix;
		},
		toggleText() {
			return this.$t(
				"login.toggleText." + this.codeMode + "." + this.alternateMode
			);
		}
	},
	methods: {
		async login() {
			this.$emit("error", null);
			this.isLoading = true;

			// clear field data that is not needed for login
			const user = { ...this.user };

			if (this.mode === "email") {
				user.password = null;
			}

			if (this.isResetForm === true) {
				user.remember = false;
			}

			try {
				await this.$api.auth.login(user);

				this.$panel.reload({
					globals: ["system", "translation"]
				});

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});
			} catch (error) {
				this.$emit("error", error);
			} finally {
				this.isLoading = false;
			}
		},
		toggle() {
			this.mode = this.alternateMode;
			this.$refs.fieldset.focus("email");
		}
	}
};
</script>

<style>
.k-login-form {
	position: relative;
}

.k-login-form label abbr {
	visibility: hidden;
}

.k-login-toggler {
	position: absolute;
	top: -2px;
	inset-inline-end: calc(var(--spacing-2) * -1);
	color: var(--link-color);
	text-decoration: underline;
	text-decoration-color: var(--link-color);
	text-underline-offset: 1px;
	height: var(--height-xs);
	line-height: 1;
	padding-inline: var(--spacing-2);
	border-radius: var(--rounded);
	z-index: 1;
}
</style>
