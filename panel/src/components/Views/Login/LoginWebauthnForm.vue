<script>
import webauthn from "@/helpers/webauthn.ts";

/**
 * Shared logic for the WebAuthn login forms
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @internal
 */
export default {
	props: {
		publicKey: Object
	},
	emits: ["error", "submit"],
	data() {
		return {
			loading: false
		};
	},
	methods: {
		async authenticate(field) {
			if (webauthn.isSupported() === false) {
				this.$emit("error", {
					message: this.$t("error.login.webauthn.unsupported")
				});
				return;
			}

			if (!this.publicKey) {
				this.$emit("error", {
					message: this.$t("error.login.webauthn.unavailable")
				});
				return;
			}

			this.loading = true;

			await webauthn.get(
				this.publicKey,
				(assertion) =>
					this.$emit("submit", { [field]: JSON.stringify(assertion) }),
				(message) => this.$emit("error", { message })
			);

			this.loading = false;
		}
	}
};
</script>
