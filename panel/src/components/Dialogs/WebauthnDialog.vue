<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		:cancel-button="{ text: $t('confirm'), icon: 'check' }"
		:submit-button="false"
		size="medium"
		class="k-webauthn-dialog"
		@cancel="$emit('cancel')"
		@submit.prevent="create"
	>
		<k-box v-if="!isSupported" icon="fingerprint" theme="negative">
			Passkeys are not supported in this browser.
		</k-box>

		<k-stack v-else gap="var(--spacing-6)">
			<k-dialog-text
				text="Passkeys let you sign in without a password. Create a passkey on this device and remove old ones below."
			/>

			<k-text-field
				:counter="false"
				:disabled="isCreating"
				:value="label"
				label="Create a new passkey"
				placeholder="Give it a name, e.g. MacBook"
				@input="label = $event"
			>
				<template #after>
					<k-button
						:disabled="isCreating"
						:icon="isCreating ? 'loader' : 'fingerprint'"
						:text="$t('create')"
						theme="positive"
						variant="filled"
						size="xs"
						@click="create"
					/>
				</template>
			</k-text-field>

			<k-section label="Saved passkeys">
				<k-empty v-if="credentials.length === 0" icon="fingerprint">
					No passkeys yet
				</k-empty>

				<k-definitions v-else>
					<k-definition
						v-for="credential in credentials"
						:key="credential.id"
						:term="credential.name ?? 'Passkey'"
					>
						{{ $library.dayjs(credential.createdAt * 1000) }}

						<k-button
							:disabled="isRemoving === credential.id"
							:icon="isRemoving === credential.id ? 'loader' : 'trash'"
							:title="$t('delete')"
							theme="negative"
							variant="filled"
							size="xs"
							@click="remove(credential.id)"
						/>
					</k-definition>
				</k-definitions>
			</k-section>
		</k-stack>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import {
	normalizePublicKeyOptions,
	serializeAttestationCredential,
	supported
} from "@/helpers/webauthn.js";

export default {
	mixins: [Dialog],
	props: {
		credentials: {
			type: Array,
			default: () => []
		},
		options: {
			type: Object,
			required: true
		}
	},
	emits: ["cancel"],
	data() {
		return {
			isCreating: false,
			isRemoving: false,
			label: ""
		};
	},
	computed: {
		isSupported() {
			return supported();
		}
	},
	methods: {
		async create() {
			this.isCreating = true;

			try {
				const options = normalizePublicKeyOptions(this.options);
				const credential = await navigator.credentials.create(options);

				await this.$panel.dialog.post({
					action: "create",
					name: this.label,
					credential: serializeAttestationCredential(credential)
				});

				this.label = "";
				await this.$panel.dialog.refresh();
			} catch (error) {
				console.error(error?.message ?? error);
			} finally {
				this.isCreating = false;
			}
		},
		async remove(id) {
			this.isRemoving = id;

			try {
				await this.$panel.dialog.post({
					action: "remove",
					id
				});
				await this.$panel.dialog.refresh();
			} catch (error) {
				this.$panel.notification.error(error?.message || error);
			} finally {
				this.isRemoving = false;
			}
		}
	}
};
</script>

<style>
.k-webauthn-dialog .k-definition dd {
	color: var(--color-text-dimmed);
	display: flex;
	justify-content: space-between;
}
</style>
