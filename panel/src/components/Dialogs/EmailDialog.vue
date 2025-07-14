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
import { props as FieldsProps } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, FieldsProps],
	props: {
		// eslint-disable-next-line vue/require-prop-types
		fields: {
			default: () => ({
				href: {
					label: window.panel.t("email"),
					type: "email",
					icon: "email"
				},
				title: {
					label: window.panel.t("title"),
					type: "text",
					icon: "title"
				}
			})
		},
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "medium"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: () => window.panel.t("insert")
		}
	},
	emits: ["cancel", "submit"],
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
