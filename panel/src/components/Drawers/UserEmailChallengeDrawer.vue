<template>
	<k-drawer
		ref="drawer"
		v-bind="$props"
		class="k-user-email-challenge-drawer"
		@cancel="$emit('cancel')"
		@submit="$emit('cancel')"
	>
		<form
			ref="form"
			class="k-stack"
			style="gap: var(--spacing-6)"
			@submit.prevent="onSubmit"
		>
			<k-user-info :label="$t('account')" :user="user" />

			<k-drawer-text :text="$t('login.challenge.email.description')" />

			<template v-if="isAccount">
				<!-- 1. start the process, which sends the code -->
				<k-button
					v-if="hasCode === false"
					:disabled="isLoading"
					:icon="isLoading ? 'loader' : actionIcon"
					:text="actionText"
					:theme="actionTheme"
					variant="filled"
					@click="start"
				/>

				<!-- 2. confirm with the code that was sent -->
				<template v-else>
					<k-text-field
						:counter="false"
						:help="$t('login.challenge.email.help')"
						:label="codeLabel"
						:placeholder="$t('login.code.placeholder.email')"
						:required="true"
						:value="code"
						autocomplete="one-time-code"
						font="monospace"
						@input="code = $event"
					/>

					<k-button
						:disabled="isLoading"
						:icon="isLoading ? 'loader' : 'check'"
						:text="actionText"
						:theme="actionTheme"
						variant="filled"
						@click="confirm"
					/>
				</template>
			</template>

			<!-- an admin managing another user re-enters their own password -->
			<k-button
				v-else-if="isEnabled"
				:disabled="isLoading"
				:icon="isLoading ? 'loader' : 'unlock'"
				:text="$t('disable')"
				theme="negative"
				variant="filled"
				@click="disableAsAdmin"
			/>

			<k-empty v-else icon="email-unread">
				{{ $t("login.challenge.email.empty") }}
			</k-empty>
		</form>
	</k-drawer>
</template>

<script>
import UserCredentialDrawer from "./UserCredentialDrawer.vue";

/**
 * Drawer to enable/disable codes via email as a second factor
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	extends: UserCredentialDrawer,
	props: {
		isEnabled: Boolean
	},
	emits: ["cancel", "submit"],
	data() {
		return {
			code: "",
			hasCode: false
		};
	},
	computed: {
		actionIcon() {
			return this.isEnabled ? "unlock" : "lock";
		},
		actionText() {
			return this.isEnabled ? this.$t("disable") : this.$t("activate");
		},
		actionTheme() {
			return this.isEnabled ? "negative" : "positive";
		},
		codeLabel() {
			if (this.isEnabled === true) {
				return this.$t("login.challenge.email.disable.label");
			}

			return this.$t("login.challenge.email.enable.label");
		}
	},
	watch: {
		isEnabled() {
			// the challenge was just enabled or disabled, so the
			// completed step must not stay on screen
			this.reset();
		}
	},
	methods: {
		async confirm() {
			if (this.$refs.form.reportValidity()) {
				await this.request(this.isEnabled ? "remove" : "create", {
					authorization: this.code
				});
			}
		},
		disableAsAdmin() {
			// an admin managing another user re-enters their own password
			this.confirmPassword({
				text: this.$t("login.challenge.email.disable.confirm", {
					user: this.$helper.string.escapeHTML(this.user.email)
				}),
				button: {
					text: this.$t("disable"),
					icon: "unlock"
				},
				onSubmit: (password) => this.request("remove", { password })
			});
		},
		onSubmit() {
			if (this.isAccount === true && this.hasCode === true) {
				this.confirm();
			}
		},
		reset() {
			this.code = "";
			this.hasCode = false;
		},
		async start() {
			this.isLoading = true;

			try {
				const response = await this.$panel.drawer.post({ action: "code" });

				if (response === false) {
					return;
				}

				this.hasCode = true;
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>
