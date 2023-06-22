<template>
	<k-text-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<slot />
	</k-text-dialog>
</template>

<script>
import TextDialog from "./TextDialog.vue";

export default {
	mixins: [TextDialog],
	props: {
		icon: {
			default: "trash"
		},
		submitButton: {
			default: () => window.panel.$t("delete")
		},
		theme: {
			default: "negative"
		}
	}
};
</script>
