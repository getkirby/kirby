<template>
	<div class="k-toggle-field-preview">
		<k-toggle-input
			:text="text"
			:value="value"
			@input="$emit('input', $event)"
			@click.native.stop
		/>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	emits: ["input"],
	computed: {
		text() {
			return this.column.text !== false ? this.field.text : null;
		}
	}
};
</script>

<style>
.k-toggle-field-preview {
	padding-inline: var(--table-cell-padding);
}
</style>
