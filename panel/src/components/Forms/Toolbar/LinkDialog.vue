<template>
	<k-form-dialog
		v-bind="$props"
		:value="values"
		@cancel="$emit('cancel')"
		@input="values = $event"
		@submit="submit"
	/>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Fields } from "@/components/Dialogs/Elements/Fields.vue";

export default {
	mixins: [Dialog, Fields],
	props: {
		fields: {
			default: () => ({
				url: {
					label: window.panel.$t("link"),
					type: "link",
					placeholder: window.panel.$t("url.placeholder"),
					icon: "url"
				},
				text: {
					label: window.panel.$t("link.text"),
					type: "text"
				}
			})
		},
		size: {
			default: "medium"
		},
		submitButton: {
			default: () => window.panel.$t("insert")
		}
	},
	data() {
		return {
			values: {
				url: null,
				text: null,
				...this.value
			}
		};
	},
	methods: {
		submit() {
			const email = this.values.email ?? "";

			// KirbyText
			if (this.$panel.config.kirbytext) {
				if (this.values.text?.length > 0) {
					return this.$emit(
						"submit",
						`(link: ${this.values.url} text: ${this.values.text})`
					);
				}

				return this.$emit("submit", `(link: ${this.values.url})`);
			}

			// Markdown
			if (this.values.text?.length > 0) {
				return this.$emit(
					"submit",
					`[${this.values.text}](${this.valuess.url})`
				);
			}

			return this.$emit("submit", `<${this.values.url}>`);
		}
	}
};
</script>
