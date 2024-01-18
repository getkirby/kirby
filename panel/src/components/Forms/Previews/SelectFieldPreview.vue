<template>
	<p class="k-select-field-preview" @click.stop>
		<k-select-input
			v-bind="field"
			:value="value"
			@input="$emit('input', $event)"
		/>
	</p>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false
};
</script>

<style>
.k-select-field-preview .k-select-input {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
}
.k-select-field-preview .k-select-input:focus-within {
	outline-offset: -2px;
	outline: var(--outline);
}
</style>
