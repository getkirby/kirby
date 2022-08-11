<template>
	<p class="k-text-field-preview" :class="$options.class">
		{{ column.before }}
		<slot>{{ text }}</slot>
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
.k-text-field-preview {
	padding: 0.325rem 0.75rem;
	overflow-x: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
</style>
