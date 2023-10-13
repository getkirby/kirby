<template>
	<div class="k-html-field-preview" :class="$options.class">
		{{ column.before }}
		<k-text :html="html" />
		{{ column.after }}
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		value: String
	},
	computed: {
		html() {
			return this.value;
		}
	}
};
</script>

<style>
.k-html-field-preview {
	padding: 0.375rem var(--table-cell-padding);
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
