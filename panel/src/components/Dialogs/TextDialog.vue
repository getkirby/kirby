<template>
	<k-dialog ref="dialog" v-bind="$props" v-on="$listeners">
		<slot>
			<k-dialog-text :text="text" />
		</slot>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Text } from "./Elements/Text.vue";

export const props = {
	mixins: [Dialog, Text]
};

export default {
	mixins: [props]
};
</script>
