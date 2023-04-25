<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@close="$emit('close')"
		@ready="$emit('ready')"
		@submit="submit"
	>
		<k-dialog-text v-if="text" :text="text" />
		<k-dialog-fields
			:fields="fields"
			:novalidate="novalidate"
			:value="model"
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
	},
	data() {
		return {
			// Since fiber dialogs don't update their `value` prop
			// on an emitted `input` event, we need to ensure a local
			// state of all updated values
			model: this.value
		};
	},
	watch: {
		value(value) {
			this.model = value;
		}
	},
	methods: {
		input(values) {
			// Since fiber dialogs don't update their `value` prop
			// we need to update our local  state ourselves, so that `k-form`
			// received up-to-date data
			this.model = values;
			this.$emit("input", this.model);
		},
		submit() {
			this.$emit("submit", this.model);
		}
	}
};
</script>
