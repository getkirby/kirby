<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
	>
		<slot>
			<k-dialog-text v-if="text" :text="text" />
			<k-dialog-fields
				:fields="fields"
				:value="value"
				@input="$emit('input', $event)"
				@submit="$emit('submit', $event)"
			/>
		</slot>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as FieldsProps } from "./Elements/Fields.vue";

export default {
	mixins: [Dialog, FieldsProps],
	props: {
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "medium"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: () => window.panel.t("save")
		},
		text: {
			type: String
		}
	},
	emits: ["cancel", "input", "submit"]
};
</script>
