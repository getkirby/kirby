<template>
	<k-fieldset
		v-if="hasFields"
		:fields="fields"
		:value="formValue"
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
			default: () => window.panel.t("dialog.fields.empty"),
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
		formValue() {
			return {
				...this.$helper.field.form(this.fields),
				...this.value
			};
		},
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
