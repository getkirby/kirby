<template>
	<form class="k-login-form" @submit.prevent="login">
		<div class="k-login-fields">
			<k-fieldset
				ref="fieldset"
				:novalidate="true"
				:fields="fields"
				:value="user"
				@input="user = $event"
			/>
		</div>

		<div class="k-login-buttons">
			<span class="k-login-checkbox">
				<k-checkbox-input
					:value="user.remember"
					:label="$t('login.remember')"
					@input="user.remember = $event"
				/>
			</span>
			<k-login-button :loading="isLoading" />
		</div>
	</form>
</template>

<script>
export default {
	emits: ["error"],
	data() {
		return {
			isLoading: false,
			user: {
				email: "",
				password: "",
				remember: false
			}
		};
	},
	computed: {
		fields() {
			return {
				email: {
					autofocus: true,
					label: this.$t("email"),
					type: "email",
					required: true,
					link: false
				},
				password: {
					label: this.$t("password"),
					type: "password",
					minLength: 8,
					required: true,
					autocomplete: "current-password",
					counter: false
				}
			};
		}
	},
	methods: {
		async login() {
			this.$emit("error", null);
			this.isLoading = true;

			try {
				await this.$api.auth.login(this.user);

				this.$reload({
					globals: ["$system", "$translation"]
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
