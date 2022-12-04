<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel', $event)"
		@close="$emit('close', $event)"
		@open="$emit('open', $event)"
		@ready="$emit('ready', $event)"
		@submit="$emit('submit', $event)"
	>
		<slot>
			<k-text v-if="text" :html="text" />
			<k-box v-else theme="negative">
				This dialog does not define any text
			</k-box>
		</slot>
	</k-dialog>
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
		text: String
	},
	emits: ["cancel", "close", "open", "ready", "submit", "success"]
};
</script>
