<template>
	<k-model-picker-dialog
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
				icon: "users",
				text: window.panel.t("dialog.users.empty")
			})
		}
	},
	emits: ["cancel", "submit"]
};
</script>
