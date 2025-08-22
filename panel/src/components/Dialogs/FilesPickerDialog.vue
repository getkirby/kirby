<template>
	<k-models-picker-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
import { props as ModelsPickerDialog } from "./ModelsPickerDialog.vue";

export default {
	mixins: [ModelsPickerDialog],
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
