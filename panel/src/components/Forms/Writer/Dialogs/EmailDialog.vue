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
				href: {
					label: window.panel.$t("email"),
					type: "email",
					icon: "email"
				},
				title: {
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
				href: "",
				title: null,
				...this.value
			}
		};
	},
	methods: {
		submit() {
			this.$emit("submit", this.values);
		}
	}
};
</script>
