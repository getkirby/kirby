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
		 * @deprecated 4.0.0 use the submit button settings instead
		 */
		disabled: {
			default: false,
			type: Boolean
		},
		/**
		 * The icon type for the submit button
		 * @deprecated 4.0.0 use the submit button settings instead
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
		 * @deprecated 4.0.0 use the submit button settings instead
		 */
		theme: {
			default: "positive",
			type: String
		}
	}
};

export default {
	mixins: [props],
	emits: ["cancel"],
	computed: {
		buttons() {
			return [
				this.button(this.cancelButton, {
					click: () => {
						this.$emit("cancel");
					},
					class: "k-dialog-button-cancel",
					icon: "cancel",
					text: this.$t("cancel"),
					variant: "filled"
				}),
				this.button(this.submitButton, {
					class: "k-dialog-button-submit",
					disabled: this.disabled || this.$panel.dialog.isLoading,
					icon: this.$panel.dialog.isLoading ? "loader" : this.icon,
					text: this.$t("confirm"),
					theme: this.theme,
					type: "submit",
					variant: "filled"
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
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: var(--spacing-3);
	--button-height: var(--height-lg);
}
</style>
