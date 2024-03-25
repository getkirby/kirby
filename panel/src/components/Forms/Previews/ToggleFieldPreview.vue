<template>
	<div :class="['k-toggle-field-preview', $attrs.class]" :style="$attrs.style">
		<k-toggle-input
			:disabled="!isEditable"
			:text="text"
			:value="value"
			@input="$emit('input', $event)"
			@click="isEditable ? $event.stopPropagation() : null"
		/>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		value: Boolean
	},
	emits: ["input"],
	computed: {
		isEditable() {
			return this.field.disabled !== true;
		},
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
