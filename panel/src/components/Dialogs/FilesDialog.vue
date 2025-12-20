<template>
	<k-models-dialog
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as ModelsDialogProps } from "./ModelsDialog.vue";

/**
 * @deprecated 6.0.0 Use `k-file-picker-dialog` instead
 */
export default {
	mixins: [Dialog, ModelsDialogProps],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "image",
				text: window.panel.t("dialog.files.empty")
			})
		}
	},
	emits: ["cancel", "submit"]
};
</script>
