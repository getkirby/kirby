<template>
	<!-- eslint-disable vue/no-mutating-props -->
	<div class="k-toggle-field-preview">
		<k-input
			:text="text"
			:value="value"
			type="toggle"
			@input="$emit('input', $event)"
		/>
	</div>
</template>

<script>
export default {
	props: {
		field: Object,
		value: Boolean,
		column: Object
	},
	computed: {
		text() {
			return this.column.text !== false ? this.field.text : null;
		}
	}
};
</script>

<style>
.k-toggle-field-preview {
	padding: 0 0.25rem 0 0.75rem;
}
</style>
