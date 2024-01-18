<template>
	<p class="k-text-field-preview" :class="$options.class" @click.stop>
		{{ column.before }}
		<k-text-input :value="value ?? ''" @input="$emit('input', $event)" />
		{{ column.after }}
	</p>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	computed: {
		text() {
			return this.value;
		}
	}
};
</script>

<style>
.k-text-field-preview input {
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
}
.k-text-field-preview input:focus {
	outline-offset: -2px;
	outline: var(--outline);
}
</style>
