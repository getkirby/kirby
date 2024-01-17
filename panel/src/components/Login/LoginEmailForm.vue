<template>
	<form class="k-login-form k-login-email-form" @submit.prevent="login">
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
			<k-login-button :loading="isLoading" :text="buttonText" />
		</div>
	</form>
</template>

<script>
export default {
	props: {
		mode: {
			default: "login",
			type: String
		}
	},
	emits: ["error"],
	data() {
		return {
			isLoading: false,
			user: {
				email: ""
			}
		};
	},
	computed: {
		buttonText() {
			return this.$t(
				"login" + (this.mode === "password-reset" ? ".reset" : "")
			);
		},
		fields() {
			return {
				email: {
					autofocus: true,
					label: this.$t("email"),
					type: "email",
					required: true,
					link: false
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
