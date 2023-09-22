<template>
	<!-- eslint-disable vue/no-mutating-props -->
	<div class="k-toggle-field-preview">
		<k-toggle-inputbox
			:text="text"
			:value="value"
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
	--inputbox-outline-focus: 0;
	--inputbox-padding: var(--spacing-3);
	--inputbox-shadow: none;
}
</style>
