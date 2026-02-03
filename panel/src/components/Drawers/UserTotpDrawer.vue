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
			<k-definitions>
				<k-definition
					:data-theme="isEnabled ? 'positive' : 'passive'"
					:term="$t('status')"
					class="k-user-totp-status"
				>
					<k-icon :type="isEnabled ? 'check' : 'cancel'" />
					{{
						isEnabled
							? $t("login.totp.enable.success")
							: $t("login.totp.disable.success")
					}}
				</k-definition>
			</k-definitions>

			<k-drawer-text :text="$t('login.totp.enable.intro')" />

			<!-- Enable -->
			<template v-if="!isEnabled">
				<k-dialog-fields
					:fields="{
						qr: {
							label: $t('login.totp.enable.qr.label'),
							type: 'info',
							text: qr,
							help: $t('login.totp.enable.qr.help', { secret: value.secret, uri }),
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

			<!-- Disable user -->
			<template v-else>
				<k-section
					v-if="isAccount"
					:label="$t('login.totp.disable.label')"
					class="k-stack"
				>
					<k-password-field
						:counter="false"
						:help="$t('login.totp.disable.help')"
						:required="true"
						@input="totp.password = $event"
					/>
				</k-section>

				<k-dialog-text
					v-else
					:text="$t('login.totp.disable.admin', { user })"
				/>

				<k-button
					:text="$t('disable')"
					:icon="isLoading ? 'loader' : 'unlock'"
					theme="negative"
					variant="filled"
					@click="remove"
				/>
			</template>
		</form>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";

export default {
	mixins: [Drawer],
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
			isLoading: false,
			totp: {}
		};
	},
	methods: {
		async create() {
			await this.request("create", this.totp);
		},
		async remove() {
			await this.request("remove", { password: this.totp.password });
		},
		async request(action, payload = {}) {
			if (this.$refs.form.reportValidity()) {
				try {
					this.isLoading = true;
					await this.$panel.drawer.post({ action, ...payload });
					await this.$panel.drawer.refresh();
				} catch (error) {
					this.$panel.notification.error(error?.message ?? error);
				} finally {
					this.isLoading = false;
				}
			}
		}
	}
};
</script>

<style>
.k-user-totp-status dd {
	--icon-color: var(--theme-color-icon);
	color: var(--theme-color-text);
}

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
.k-totp-qrcode .k-box,
.k-totp-qrcode .k-text {
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
	background-color: white;
	padding: var(--spacing-3);
	font-size: var(--text-sm);

	display: flex;
	flex-direction: column;
	justify-content: space-around;
}

.k-user-totp-drawer .k-field-name-confirm {
	--input-font-size: var(--text-xl);
	margin-block-start: -1rem;
}
</style>
