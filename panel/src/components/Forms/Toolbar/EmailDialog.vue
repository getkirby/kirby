<script>
import EmailDialog from "@/components/Dialogs/EmailDialog.vue";

export default {
	extends: EmailDialog,
	methods: {
		submit() {
			const email = this.values.href ?? "";
			const text = this.values.title ?? "";

			// KirbyText
			if (this.$panel.config.kirbytext) {
				if (text?.length > 0) {
					return this.$emit("submit", `(email: ${email} text: ${text})`);
				}

				return this.$emit("submit", `(email: ${email})`);
			}

			// Markdown
			if (text?.length > 0) {
				return this.$emit("submit", `[${text}](mailto:${email})`);
			}

			return this.$emit("submit", `<${email}>`);
		}
	}
};
</script>
