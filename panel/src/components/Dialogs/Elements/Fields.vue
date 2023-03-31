<template>
	<k-fieldset
		v-if="hasFields"
		:novalidate="novalidate"
		:fields="fields"
		:value="value"
		class="k-dialog-fields"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
	<k-box v-else theme="info">{{ empty }}</k-box>
</template>

<script>
export const props = {
	props: {
		/**
		 * Empty state message if no fields are defined
		 */
		empty: {
			type: String,
			default() {
				return window.panel.$t("dialog.fields.empty");
			}
		},
		/**
		 * An array or object with all available fields
		 */
		fields: {
			type: [Array, Object],
			default() {
				return [];
			}
		},
		/**
		 * Skip client side validation (vuelidate).
		 * Validation is skipped by default in
		 * dialogs. Native input validation still works though.
		 */
		novalidate: {
			type: Boolean,
			default: true
		},
		/**
		 * An object with all values for the fields
		 */
		value: {
			type: Object,
			default() {
				return {};
			}
		}
	}
};

export default {
	mixins: [props],
	computed: {
		hasFields() {
			return this.$helper.object.length(this.fields) > 0;
		}
	}
};
</script>

<style>
.k-dialog-fields {
	padding-bottom: 0.5rem;
}
</style>
