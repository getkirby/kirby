<template>
	<k-drawer
		ref="drawer"
		class="k-user-webauthn-drawer"
		v-bind="$props"
		size="tiny"
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

			<k-box
				v-if="!isSupported"
				:text="$t('error.login.webauthn.unsupported')"
				icon="fingerprint"
				theme="negative"
			/>

			<template v-else>
				<k-drawer-text :text="$t('login.webauthn.description')" />

				<k-section :label="$t('login.webauthn.label')">
					<k-collection
						:empty="{
							text: $t('login.webauthn.empty'),
							icon: 'fingerprint'
						}"
						:items="items"
					/>
				</k-section>

				<!-- only the account owner may register new passkeys -->
				<k-stack v-if="isAccount">
					<k-text-field
						:counter="false"
						:disabled="isLoading"
						:label="$t('login.webauthn.create')"
						:placeholder="$t('login.webauthn.create.placeholder')"
						:value="label"
						@input="label = $event"
					/>
					<k-button
						:disabled="isLoading"
						:icon="isLoading ? 'loader' : 'fingerprint'"
						:text="$t('create')"
						theme="positive"
						variant="filled"
						@click="create"
					/>
				</k-stack>
			</template>
		</form>
	</k-drawer>
</template>

<script>
import UserCredentialDrawer from "./UserCredentialDrawer.vue";
import webauthn from "@/helpers/webauthn.js";

/**
 * Drawer to create/delete auth passkeys
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	extends: UserCredentialDrawer,
	props: {
		/**
		 * WebAuthn assertion options for confirming a passkey removal
		 */
		assertion: {
			type: [Object, null],
			default: null
		},
		/**
		 * List of existing registered passkeys
		 */
		credentials: {
			type: Array,
			default: () => []
		},
		/**
		 * WebAuthn creation options for registering a new passkey
		 */
		registration: {
			type: Object,
			required: true
		}
	},
	emits: ["cancel", "submit"],
	data() {
		return {
			label: ""
		};
	},
	computed: {
		isSupported() {
			return webauthn.isSupported() === true;
		},
		items() {
			return this.credentials.map((credential) => ({
				image: { icon: "fingerprint" },
				text: credential.name ?? "–",
				options: [
					{
						icon: "trash",
						title: this.$t("delete"),
						click: () => this.onRemove(credential.id)
					}
				]
			}));
		}
	},
	methods: {
		async create() {
			this.isLoading = true;

			await webauthn.create(
				this.registration,
				async (credential) => {
					await this.request("create", { name: this.label, credential });
					this.label = "";
				},
				(error) => this.$panel.notification.error(error)
			);

			this.isLoading = false;
		},
		async onRemove(id) {
			// without assertion options this is an admin editing another
			// user, admin needs to re-enter their own password
			if (this.assertion === null) {
				this.confirmPassword({
					text: this.$t("login.webauthn.remove.confirm", {
						user: this.$helper.string.escapeHTML(this.user.email)
					}),
					button: { text: this.$t("delete") },
					onSubmit: (password) => this.request("remove", { id, password })
				});
				return;
			}

			// the account owner completes a passkey assertion
			await webauthn.get(
				this.assertion,
				(authorization) => this.request("remove", { id, authorization }),
				(error) => this.$panel.notification.error(error)
			);
		},
		onSubmit() {
			if (this.isAccount === true) {
				this.create();
			}
		}
	}
};
</script>

<style>
.k-user-webauthn-drawer .k-definition dt {
	white-space: nowrap;
}
.k-user-webauthn-drawer .k-definition dd {
	color: var(--color-text-dimmed);
	display: flex;
	justify-content: space-between;
}
</style>
