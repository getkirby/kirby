<template>
	<k-button v-bind="status" class="k-flag-field-preview" />
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		value: Object
	},
	computed: {
		status() {
			return {
				...this.$helper.page.status(this.value.status),
				...this.value
			};
		}
	}
};
</script>

<style>
.k-flag-field-preview.k-button {
	--button-height: var(--table-row-height);
	--button-width: var(--button-height);
}
</style>
