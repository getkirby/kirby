<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		size="large"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-dialog-text
			:text="$t('login.totp.enable.intro')"
			class="k-totp-dialog-intro"
		/>

		<div class="k-totp-dialog-grid">
			<div class="k-totp-qrcode">
				<k-info-field
					:label="$t('login.totp.enable.qr.label')"
					:text="qr"
					:help="$t('login.totp.enable.qr.help', { secret: value.secret })"
					theme="passive"
				/>
			</div>

			<k-dialog-fields
				:fields="{
					info: {
						label: $t('login.totp.enable.confirm.headline'),
						type: 'info',
						text: $t('login.totp.enable.confirm.text'),
						theme: 'none'
					},
					confirm: {
						label: $t('login.totp.enable.confirm.label'),
						type: 'text',
						counter: false,
						font: 'monospace',
						required: true,
						placeholder: $t('login.code.placeholder.totp'),
						help: $t('login.totp.enable.confirm.help')
					},
					secret: {
						type: 'hidden'
					}
				}"
				:novalidate="true"
				:value="value"
				class="k-totp-dialog-fields"
				@input="$emit('input', $event)"
				@submit="$emit('submit', $event)"
			/>
		</div>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Fields } from "./Elements/Fields.vue";

export const props = {
	mixins: [Dialog, Fields]
};

export default {
	mixins: [props],
	props: {
		qr: String,
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: () => ({
				text: window.panel.$t("activate"),
				icon: "lock",
				theme: "notice"
			})
		}
	}
};
</script>

<style>
.k-totp-dialog-headline {
	margin-bottom: var(--spacing-1);
}
.k-totp-dialog-intro {
	margin-bottom: var(--spacing-6);
}

.k-totp-dialog-grid {
	display: grid;
	gap: var(--spacing-6);
}

@media screen and (min-width: 40rem) {
	.k-totp-dialog-grid {
		grid-template-columns: 1fr 1fr;
		gap: var(--spacing-8);
	}
}

.k-totp-qrcode .k-box[data-theme] {
	padding: var(--box-padding-inline);
}

.k-totp-dialog-fields .k-field-name-confirm {
	--input-height: var(--height-xl);
	--input-rounded: var(--rounded-xl);
	--input-font-size: var(--text-3xl);
}
</style>