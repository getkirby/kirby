<template>
	<k-text-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel', $event)"
		@close="$emit('close', $event)"
		@open="$emit('open', $event)"
		@ready="$emit('ready', $event)"
		@submit="$emit('submit', $event)"
	>
		<slot />
	</k-text-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
	mixins: [DialogMixin],
	props: {
		/**
		 * Whether to disable the submit button
		 */
		disabled: Boolean,
		icon: {
			type: String,
			default: "trash"
		},
		submitButton: {
			type: [String, Boolean],
			default() {
				return window.panel.$t("delete");
			}
		},
		text: String,
		theme: {
			type: String,
			default: "negative"
		}
	},
	emits: ["cancel", "close", "open", "ready", "submit", "success"]
};
</script>
