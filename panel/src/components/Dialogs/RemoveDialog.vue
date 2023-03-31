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
			default: "trash"
		},
		submitButton: {
			default() {
				return window.panel.$t("delete");
			}
		},
		theme: {
			default: "negative"
		}
	}
};
</script>
