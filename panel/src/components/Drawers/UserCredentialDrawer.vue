<script>
import Drawer from "@/mixins/drawer.js";

/**
 * Shared base for drawers that manage a removable login
 * credential (a passkey, a TOTP secret).
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	mixins: [Drawer],
	props: {
		/**
		 * Whether the drawer relates to the current user's own account.
		 */
		isAccount: Boolean,
		user: Object
	},
	data() {
		return {
			isLoading: false
		};
	},
	methods: {
		/**
		 * Opens the confirmation dialog in which an admin re-enters
		 * their own password to remove another user's credential
		 */
		confirmPassword({ text, button, onSubmit }) {
			this.$panel.dialog.open({
				component: "k-form-dialog",
				props: {
					text,
					fields: {
						password: {
							type: "password",
							label: this.$t("password"),
							required: true,
							counter: false,
							autocomplete: "current-password",
							help: this.$t("account") + ": " + this.$panel.user.email
						}
					},
					submitButton: {
						icon: "trash",
						theme: "negative",
						...button
					}
				},
				on: {
					submit: ({ password }) => onSubmit(password)
				}
			});
		},
		async request(action, data = {}) {
			this.isLoading = true;

			try {
				const response = await this.$panel.drawer.post({ action, ...data });

				if (response === false) {
					return;
				}

				this.$panel.dialog.close();
				await this.$panel.drawer.refresh();
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>
