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
