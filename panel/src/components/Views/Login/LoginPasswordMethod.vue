<template>
	<form class="k-login-form" @submit.prevent="onSubmit">
		<k-fieldset
			ref="fieldset"
			:fields="fields"
			:value="user"
			xlass="k-login-fields"
			@input="user = $event"
		/>

		<footer class="k-login-buttons">
			<k-checkbox-input
				:label="$t('login.remember')"
				:checked="user.remember"
				:value="user.remember"
				@input="user.remember = $event"
			/>

			<k-button
				:disabled="isProcessing"
				:icon="isProcessing ? 'loader' : 'check'"
				:text="method === 'password-reset' ? $t('login.reset') : $t('login')"
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

export default {
	mixins: [LoginProps],
	emits: ["error"],
	data() {
		return {
			isProcessing: false,
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
		}
	},
	methods: {
		async onSubmit() {
			this.$emit("error", null);
			this.isProcessing = true;

			// clear field data that is not needed for login
			const user = { ...this.user };

			if (this.method !== "password") {
				user.password = null;
			}

			if (this.method === "password-reset") {
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
				this.isProcessing = false;
			}
		}
	}
};
</script>

<style></style>
