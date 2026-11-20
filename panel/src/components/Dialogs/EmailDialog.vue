<template>
	<k-form-dialog
		v-bind="dialog"
		:value="values"
		@cancel="$emit('cancel')"
		@input="values = $event"
		@submit="submit"
	/>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as FieldsProps } from "./Elements/Fields.vue";
import { dir, lang } from "@/mixins/props.js";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [Dialog, FieldsProps, dir, lang],
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
	computed: {
		/**
		 * Props for the form dialog, with the language
		 * of the content on the title field
		 */
		dialog() {
			const { dir, lang, ...props } = this.$props;

			if (props.fields.title) {
				props.fields = {
					...props.fields,
					title: { dir, lang, ...props.fields.title }
				};
			}

			return props;
		}
	},
	methods: {
		submit() {
			this.$emit("submit", this.values);
		}
	}
};
</script>
