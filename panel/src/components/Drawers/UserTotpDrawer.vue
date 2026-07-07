<template>
	<k-drawer
		ref="drawer"
		v-bind="$props"
		size="tiny"
		class="k-user-totp-drawer"
		@cancel="$emit('cancel')"
		@submit="$emit('cancel')"
	>
		<form ref="form" class="k-stack" style="gap: var(--spacing-8)">
			<k-drawer-text :text="$t('login.totp.enable.intro')" />

			<!-- Enable (only the account owner may set up their own factor) -->
			<template v-if="isAccount && !isEnabled">
				<k-dialog-fields
					:fields="{
						qr: {
							label: $t('login.totp.enable.qr.label'),
							type: 'info',
							text: qr,
							help: $t('login.totp.enable.qr.help', {
								secret: value.secret,
								uri
							}),
							theme: 'passive',
							class: 'k-totp-qrcode'
						},
						info: {
							label: $t('login.totp.enable.confirm.headline'),
							type: 'info',
							text: $t('login.totp.enable.confirm.text'),
							theme: 'none'
						},
						confirm: {
							type: 'text',
							autocomplete: 'one-time-code',
							required: true,
							counter: false,
							font: 'monospace',
							placeholder: $t('login.code.placeholder.totp'),
							help: $t('login.totp.enable.confirm.help')
						},
						secret: {
							type: 'hidden'
						}
					}"
					:value="value"
					@input="totp = $event"
				/>

				<k-button
					:text="$t('activate')"
					:icon="isLoading ? 'loader' : 'lock'"
					theme="positive"
					variant="filled"
					@click="create"
				/>
			</template>

			<!-- Disable -->
			<template v-else>
				<!-- the account owner enters a current code to confirm -->
				<k-text-field
					v-if="isAccount"
					:counter="false"
					:label="$t('login.totp.disable.label')"
					:placeholder="$t('login.code.placeholder.totp')"
					:required="true"
					:value="code"
					autocomplete="one-time-code"
					font="monospace"
					@input="code = $event"
				/>

				<k-button
					:disabled="isLoading"
					:icon="isLoading ? 'loader' : 'unlock'"
					:text="$t('disable')"
					theme="negative"
					variant="filled"
					@click="disable"
				/>
			</template>
		</form>
	</k-drawer>
</template>

<script>
import UserCredentialDrawer from "./UserCredentialDrawer.vue";

/**
 * Drawer to enable/disable auth TOTP
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	extends: UserCredentialDrawer,
	props: {
		isAccount: Boolean,
		isEnabled: Boolean,
		/**
		 * SVG element for QR code containing TOTP secret
		 */
		qr: {
			type: String,
			required: true
		},
		uri: String,
		user: String,
		value: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["cancel", "submit"],
	data() {
		return {
			code: "",
			totp: {}
		};
	},
	methods: {
		async create() {
			if (this.$refs.form.reportValidity()) {
				await this.request("create", this.totp);
			}
		},
		disable() {
			// the account owner proves control with a current code
			if (this.isAccount === true) {
				this.remove({ authorization: this.code });
				return;
			}

			// an admin managing another user re-enters their own password
			this.confirmPassword({
				text: this.$t("login.totp.disable.admin", { user: this.user }),
				button: {
					text: this.$t("disable"),
					icon: "unlock"
				},
				onSubmit: (password) => this.request("remove", { password })
			});
		}
	}
};
</script>

<style>
.k-totp-qrcode {
	display: grid;
	grid-template-columns: auto 1fr;
	grid-template-rows: auto auto;
}
.k-totp-qrcode .k-headline {
	grid-column: 1 / -1;
}
.k-totp-qrcode .k-box[data-theme] {
	padding: 1px;
	max-width: 14rem;
}
.k-totp-qrcode .k-box[data-theme],
.k-totp-qrcode .k-text img {
	border-radius: 0;
	border-top-left-radius: var(--rounded);
	border-bottom-left-radius: var(--rounded);
	overflow: hidden;
}
.k-totp-qrcode .k-field-footer {
	margin-top: 0;
	border: 1px solid var(--color-border);
	border-left: 0;
	border-top-right-radius: var(--rounded);
	border-bottom-right-radius: var(--rounded);
	background-color: var(--input-color-back);
	padding: var(--spacing-3);
	font-size: var(--text-sm);

	display: flex;
	flex-direction: column;
	justify-content: space-around;
}

.k-user-totp-drawer .k-field-name-confirm {
	margin-block-start: -1rem;
}
</style>
