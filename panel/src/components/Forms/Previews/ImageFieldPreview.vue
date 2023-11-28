<template>
	<k-item-image v-if="value" class="k-image-field-preview" :image="value" />
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		value: [Object]
	}
};
</script>

<style>
.k-image-field-preview {
	height: 100%;
}
</style>
