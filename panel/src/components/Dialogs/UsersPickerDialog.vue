<template>
	<k-models-picker-dialog
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
				icon: "users",
				text: window.panel.t("dialog.users.empty")
			})
		}
	},
	emits: ["cancel", "submit"]
};
</script>
