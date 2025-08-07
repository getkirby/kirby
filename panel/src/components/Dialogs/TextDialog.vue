<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<slot>
			<k-dialog-text :text="text" />
		</slot>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as TextProps } from "./Elements/Text.vue";

export const props = {
	mixins: [Dialog, TextProps]
};

export default {
	mixins: [props],
	emits: ["cancel", "submit"]
};
</script>
