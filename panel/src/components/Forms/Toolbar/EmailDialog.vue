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
				email: {
					label: window.panel.$t("email"),
					type: "email",
					icon: "email"
				},
				text: {
					label: window.panel.$t("link.text"),
					type: "text",
					icon: "title"
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
				email: null,
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
						`(email: ${email} text: ${this.values.text})`
					);
				}

				return this.$emit("submit", `(email: ${email})`);
			}

			// Markdown
			if (this.values.text?.length > 0) {
				return this.$emit("submit", `[${this.values.text}](mailto:${email})`);
			}

			return this.$emit("submit", `<${email}>`);
		}
	}
};
</script>
