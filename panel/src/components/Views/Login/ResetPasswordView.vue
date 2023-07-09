<template>
	<k-panel-inside class="k-password-reset-view">
		<k-form
			:fields="fields"
			:submit-button="$t('change')"
			:value="values"
			@input="values = $event"
			@submit="submit"
		>
			<template #header>
				<h1 class="sr-only">
					{{ $t("view.resetPassword") }}
				</h1>

				<k-login-alert v-if="issue" @click="issue = null">
					{{ issue }}
				</k-login-alert>

				<k-user-info :user="$panel.user" />
			</template>

			<template #footer>
				<div class="k-login-buttons">
					<k-button
						icon="check"
						theme="notice"
						type="submit"
						variant="filled"
						class="k-login-button"
					>
						{{ $t("change") }} <template v-if="isLoading"> … </template>
					</k-button>
				</div>
			</template>
		</k-form>
	</k-panel-inside>
</template>

<script>
// import the Login View to load the styles
import "./LoginView.vue";

export default {
	data() {
		return {
			isLoading: false,
			issue: "",
			values: {
				password: null,
				passwordConfirmation: null
			}
		};
	},
	computed: {
		fields() {
			return {
				password: {
					autofocus: true,
					label: this.$t("user.changePassword.new"),
					icon: "key",
					type: "password"
				},
				passwordConfirmation: {
					label: this.$t("user.changePassword.new.confirm"),
					icon: "key",
					type: "password"
				}
			};
		}
	},
	mounted() {
		this.$panel.title = this.$t("view.resetPassword");
	},
	methods: {
		async submit() {
			if (!this.values.password || this.values.password.length < 8) {
				this.issue = this.$t("error.user.password.invalid");
				return false;
			}

			if (this.values.password !== this.values.passwordConfirmation) {
				this.issue = this.$t("error.user.password.notSame");
				return false;
			}

			this.isLoading = true;

			try {
				await this.$api.users.changePassword(
					this.$panel.user.id,
					this.values.password
				);

				this.$panel.notification.success();
				this.$go("/");
			} catch (error) {
				this.issue = error.message;
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>

<style>
.k-password-reset-view .k-user-info {
	height: var(--height-xl);
	margin-top: var(--spacing-12);
	margin-bottom: var(--spacing-8);
	padding: var(--spacing-2);
	background: var(--color-white);
	border-radius: var(--rounded-xs);
	box-shadow: var(--shadow);
}
</style>
