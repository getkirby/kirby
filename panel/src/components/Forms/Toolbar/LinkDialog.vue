<script>
import LinkDialog from "@/components/Dialogs/LinkDialog.vue";

export default {
	extends: LinkDialog,
	props: {
		// eslint-disable-next-line vue/require-prop-types
		fields: {
			default: () => ({
				href: {
					label: window.panel.t("link"),
					type: "link",
					placeholder: window.panel.t("url.placeholder"),
					icon: "url"
				},
				title: {
					label: window.panel.t("link.text"),
					type: "text",
					icon: "title"
				}
			})
		}
	},
	methods: {
		submit() {
			const url = this.values.href ?? "";
			const text = this.values.title ?? "";

			// KirbyText
			if (this.$panel.config.kirbytext) {
				if (text?.length > 0) {
					return this.$emit("submit", `(link: ${url} text: ${text})`);
				}

				return this.$emit("submit", `(link: ${url})`);
			}

			// Markdown
			if (text?.length > 0) {
				return this.$emit("submit", `[${text}](${url})`);
			}

			return this.$emit("submit", `<${url}>`);
		}
	}
};
</script>
