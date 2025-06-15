<template>
	<k-button-group class="k-dialog-buttons">
		<k-button v-if="cancel" v-bind="cancel" />
		<k-button v-if="submit" v-bind="submit" />
	</k-button-group>
</template>

<script>
import { isObject } from "@/helpers/object.js";

export const props = {
	props: {
		/**
		 * Options for the cancel button
		 */
		cancelButton: {
			default: true,
			type: [Boolean, String, Object]
		},
		/**
		 * Whether to disable the submit button
		 * @deprecated 4.0.0 use the `submit-button` prop instead
		 */
		disabled: {
			default: false,
			type: Boolean
		},
		/**
		 * The icon type for the submit button
		 * @deprecated 4.0.0 use the `submit-button` prop instead
		 */
		icon: {
			default: "check",
			type: String
		},
		/**
		 * Options for the submit button
		 */
		submitButton: {
			type: [Boolean, String, Object],
			default: true
		},
		/**
		 * The theme of the submit button
		 * @values "positive", "negative"
		 * @deprecated 4.0.0 use the `submit-button` prop instead
		 */
		theme: {
			default: "positive",
			type: String
		}
	}
};

/**
 * @displayName DialogButtons
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["cancel"],
	computed: {
		cancel() {
			return this.button(this.cancelButton, {
				click: () => this.$emit("cancel"),
				class: "k-dialog-button-cancel",
				icon: "cancel",
				text: this.$t("cancel"),
				variant: "filled"
			});
		},
		submit() {
			return this.button(this.submitButton, {
				class: "k-dialog-button-submit",
				disabled: this.disabled || this.$panel.dialog.isLoading,
				icon: this.$panel.dialog.isLoading ? "loader" : this.icon,
				text: this.$t("confirm"),
				theme: this.theme,
				type: "submit",
				variant: "filled"
			});
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
