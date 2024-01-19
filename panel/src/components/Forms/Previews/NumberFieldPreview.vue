<template>
	<p class="k-number-field-preview" @click.stop>
		{{ column.before }}
		<k-number-input
			v-bind="field"
			:value="value"
			@input="$emit('input', $event)"
		/>
		{{ column.after }}
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
.k-number-field-preview input {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
}
.k-number-field-preview input:focus {
	outline-offset: -2px;
	outline: var(--outline);
}
</style>
