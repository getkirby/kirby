<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@close="$emit('close')"
		@ready="$emit('ready')"
		@submit="$refs.form.submit()"
	>
		<template v-if="text">
			<k-text :html="text" />
		</template>
		<k-form
			v-if="hasFields"
			ref="form"
			:value="model"
			:fields="fields"
			:novalidate="novalidate"
			@input="onInput"
			@submit="onSubmit"
		/>
		<k-box v-else theme="negative"> This form dialog has no fields </k-box>
	</k-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
	mixins: [DialogMixin],
	props: {
		/**
		 * Whether to disable the submit button
		 */
		disabled: Boolean,
		fields: {
			type: [Array, Object],
			default() {
				return [];
			}
		},
		novalidate: {
			type: Boolean,
			default: true
		},
		size: {
			type: String,
			default: "medium"
		},
		submitButton: {
			type: [String, Boolean],
			default() {
				return window.panel.$t("save");
			}
		},
		text: {
			type: String
		},
		theme: {
			type: String,
			default: "positive"
		},
		value: {
			type: Object,
			default() {
				return {};
			}
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
	computed: {
		hasFields() {
			return Object.keys(this.fields).length > 0;
		}
	},
	watch: {
		value(value) {
			this.model = value;
		}
	},
	methods: {
		onInput(values) {
			// Since fiber dialogs don't update their `value` prop
			// we need to update our local  state ourselves, so that `k-form`
			// received up-to-date data
			this.model = values;
			this.$emit("input", values);
		},
		onSubmit(values) {
			this.$emit("submit", values);
		}
	}
};
</script>
