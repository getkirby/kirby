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
			default: () => window.panel.$t("dialog.fields.empty"),
			type: String
		},
		/**
		 * An array or object with all available fields
		 */
		fields: {
			default: () => [],
			type: [Array, Object]
		},
		/**
		 * Skip client side validation.
		 * Validation is skipped by default in
		 * dialogs. Native input validation still works though.
		 */
		novalidate: {
			default: true,
			type: Boolean
		},
		/**
		 * An object with all values for the fields
		 */
		value: {
			default: () => ({}),
			type: Object
		}
	}
};

/**
 * @displayName DialogFields
 * @since 4.0.0
 */
export default {
	mixins: [props],
	emits: ["input", "submit"],
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
	container-type: inline-size;
}
</style>
