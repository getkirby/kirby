<template>
	<div v-if="value" class="k-flag-field-preview">
		<k-button v-bind="status" size="md" />
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		value: Object
	},
	computed: {
		status() {
			return {
				...this.$helper.page.status(this.value?.status),
				...this.value
			};
		}
	}
};
</script>

<style>
.k-flag-field-preview {
	display: flex;
	justify-content: center;
	align-items: center;
	width: var(--table-row-height);
}
</style>
