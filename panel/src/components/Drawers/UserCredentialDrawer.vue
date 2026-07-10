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
			try {
				this.isLoading = true;
				await this.$panel.drawer.post({ action, ...data });
				this.$panel.dialog.close();
				await this.$panel.drawer.refresh();
			} catch (error) {
				this.$panel.notification.error(error?.message ?? error);
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>
