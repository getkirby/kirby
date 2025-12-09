<template>
	<k-model-picker-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
import { props as ModelPickerDialog } from "./ModelPickerDialog.vue";

export default {
	mixins: [ModelPickerDialog],
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
