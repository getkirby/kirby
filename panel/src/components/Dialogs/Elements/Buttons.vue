<template>
	<k-button-group :buttons="buttons" class="k-dialog-buttons" />
</template>

<script>
import { isObject } from "@/helpers/object.js";

export const props = {
	props: {
		/**
		 * Cancel Button Settings
		 */
		cancelButton: {
			default: true,
			type: [Boolean, String, Object]
		},
		/**
		 * Whether to disable the submit button
		 * @deprecated use the submit button settings instead
		 */
		disabled: {
			default: false,
			type: Boolean
		},
		/**
		 * The icon type for the submit button
		 * @deprecated use the submit button settings instead
		 */
		icon: {
			default: "check",
			type: String
		},
		/**
		 * Submit button settings
		 */
		submitButton: {
			type: [Boolean, String, Object],
			default: true
		},
		/**
		 * The theme of the submit button
		 * @values positive, negative
		 * @deprecated use the submit button settings instead
		 */
		theme: {
			default: "positive",
			type: String
		}
	}
};

export default {
	mixins: [props],
	computed: {
		buttons() {
			return [
				this.button(this.cancelButton, {
					click: () => {
						this.$emit("cancel");
					},
					class: "k-dialog-button-cancel",
					icon: "cancel",
					text: this.$t("cancel")
				}),
				this.button(this.submitButton, {
					class: "k-dialog-button-submit",
					disabled: this.disabled,
					icon: this.icon,
					text: this.$t("confirm"),
					theme: this.theme,
					type: "submit"
				})
			].filter((button) => button !== false);
		}
	},
	methods: {
		button(button, defaults) {
			if (typeof button === "string") {
				return {
					...defaults,
					text: button
				};
			}

			if (button === false) {
				return false;
			}

			if (isObject(button) === false) {
				return defaults;
			}

			return {
				...defaults,
				...button
			};
		}
	}
};
</script>

<style>
.k-button-group.k-dialog-buttons {
	display: flex;
	margin: 0;
	justify-content: space-between;
}
.k-button-group.k-dialog-buttons .k-button {
	padding: 0.75rem 1rem;
	line-height: 1.25rem;
}
.k-button-group.k-dialog-buttons .k-button.k-dialog-button-cancel {
	text-align: start;
	padding-inline-start: 1.5rem;
}
.k-button-group.k-dialog-buttons .k-button.k-dialog-button-submit {
	text-align: end;
	padding-inline-end: 1.5rem;
}
</style>
