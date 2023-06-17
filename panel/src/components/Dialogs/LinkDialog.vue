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
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, Fields],
	props: {
		fields: {
			default: () => ({
				href: {
					label: window.panel.$t("link"),
					type: "link",
					placeholder: window.panel.$t("url.placeholder"),
					icon: "url"
				},
				title: {
					label: window.panel.$t("title"),
					type: "text",
					icon: "title"
				},
				target: {
					label: window.panel.$t("open.newWindow"),
					type: "toggle",
					text: [window.panel.$t("no"), window.panel.$t("yes")]
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
				href: "",
				title: null,
				...this.value,
				target: Boolean(this.value.target ?? false)
			}
		};
	},
	methods: {
		submit() {
			const href = this.values.href
				.replace("file://", "/@/file/")
				.replace("page://", "/@/page/");

			this.$emit("submit", {
				...this.values,
				href: href,
				target: this.values.target ? "_blank" : null
			});
		}
	}
};
</script>
