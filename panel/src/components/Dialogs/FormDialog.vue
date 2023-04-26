<template>
	<k-dialog ref="dialog" v-bind="$props" @cancel="cancel" @submit="submit">
		<k-dialog-text v-if="text" :text="text" />
		<k-dialog-fields
			:fields="fields"
			:novalidate="novalidate"
			:value="value"
			@input="input"
			@submit="submit"
		/>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as Fields } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, Fields],
	props: {
		size: {
			default: "medium",
			type: String
		},
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("save")
		},
		text: {
			type: String
		}
	}
};
</script>
