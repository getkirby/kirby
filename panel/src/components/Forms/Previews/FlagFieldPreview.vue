<template>
	<k-button v-bind="$helper.page.status(value)" class="k-flag-field-preview" />
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
