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
		// eslint-disable-next-line vue/require-prop-types
		icon: {
			default: "trash"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: () => window.panel.t("delete")
		},
		// eslint-disable-next-line vue/require-prop-types
		theme: {
			default: "negative"
		}
	},
	emits: ["cancel", "submit"]
};
</script>
