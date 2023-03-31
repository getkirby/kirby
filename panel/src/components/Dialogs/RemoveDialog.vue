<template>
	<k-text-dialog ref="dialog" v-bind="$props" v-on="$listeners">
		<slot />
	</k-text-dialog>
</template>

<script>
import TextDialog from "./TextDialog.vue";

export default {
	mixins: [TextDialog],
	props: {
		icon: {
			default: "trash",
			type: String
		},
		submitButton: {
			default: () => window.panel.$t("delete"),
			type: String
		},
		theme: {
			default: "negative",
			type: String
		}
	}
};
</script>
