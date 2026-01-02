<template>
	<form class="k-login-form" @submit.prevent="login">
		<div class="k-login-fields">
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
				:disabled="isLoading"
				:icon="isLoading ? 'loader' : 'check'"
				:text="isResetForm ? $t('login.reset') : $t('login')"
				class="k-login-button"
				size="lg"
				theme="positive"
				type="submit"
				variant="filled"
			/>
		</footer>
	</form>
</template>

<script>
import { props as LoginProps } from "./LoginView.vue";

/**
 * @deprecated 6.0.0 Use `k-login-password-method` instead
 */
export default {
	mixins: [LoginProps],
	emits: ["error"],
	data() {
		return {
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

			if (this.method === "password") {
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
		isResetForm() {
			return this.method === "password-reset";
		}
	},
	methods: {
		focus() {
			this.$refs.fieldset.focus("email");
		},
		async login() {
			this.$emit("error", null);
			this.isLoading = true;

			// clear field data that is not needed for login
			const user = { ...this.user };

			if (this.method !== "password") {
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
		}
	}
};
</script>

<style>
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
