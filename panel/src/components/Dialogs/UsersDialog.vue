<template>
	<k-models-dialog
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as ModelsDialog } from "./ModelsDialog.vue";

export default {
	mixins: [Dialog, ModelsDialog],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "users",
				text: window.panel.$t("dialog.users.empty")
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
	}
};
</script>
