<template>
	<k-status-icon class="k-flag-field-preview" v-bind="value" />
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		value: Object
	}
};
</script>

<style>
.k-flag-field-preview {
	height: var(--table-row-height);
	width: var(--table-row-height);
	display: flex;
	justify-content: center;
	align-items: center;
}
</style>
