<template>
	<k-button
		v-if="value"
		v-bind="status"
		:class="['k-flag-field-preview', $attrs.class]"
		:style="$attrs.style"
		size="md"
	/>
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
	--button-height: var(--table-row-height);
	--button-width: 100%;
	outline-offset: -2px;
}
</style>
