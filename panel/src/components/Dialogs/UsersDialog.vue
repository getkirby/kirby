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
 * @deprecated 6.0.0 Use `k-user-picker-dialog` instead
 */
export default {
	mixins: [Dialog, ModelsDialogProps],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "users",
				text: window.panel.t("dialog.users.empty")
			})
		},
		item: {
			type: Function,
			default: (item) => ({
				...item,
				key: item.email,
				info: item.info !== item.text ? item.info : null
			})
		}
	},
	emits: ["cancel", "submit"]
};
</script>
