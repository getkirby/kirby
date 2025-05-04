<template>
	<k-panel-inside class="k-password-reset-view">
		<form @submit.prevent="submit">
			<k-header>
				{{ $t("view.resetPassword") }}

				<template #buttons>
					<k-button
						icon="check"
						theme="notice"
						type="submit"
						variant="filled"
						size="sm"
					>
						{{ $t("change") }} <template v-if="isLoading"> … </template>
					</k-button>
				</template>
			</k-header>
			<k-user-info :user="$panel.user" />
			<k-fieldset :fields="fields" :value="values" />
		</form>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	data() {
		return {
			isLoading: false,
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
					type: "password",
					width: "1/2"
				},
				passwordConfirmation: {
					label: this.$t("user.changePassword.new.confirm"),
					icon: "key",
					type: "password",
					width: "1/2"
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
				return this.$panel.notification.error(
					this.$t("error.user.password.invalid")
				);
			}

			if (this.values.password !== this.values.passwordConfirmation) {
				return this.$panel.notification.error(
					this.$t("error.user.password.notSame")
				);
			}

			this.isLoading = true;

			try {
				await this.$api.users.changePassword(
					this.$panel.user.id,
					null,
					this.values.password
				);

				this.$panel.notification.success();
				this.$go("/");
			} catch (error) {
				this.$panel.notification.error(error);
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>

<style>
.k-password-reset-view .k-user-info {
	margin-bottom: var(--spacing-8);
}
</style>
