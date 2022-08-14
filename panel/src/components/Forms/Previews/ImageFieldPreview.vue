<template>
	<k-item-image class="k-image-field-preview" :image="value" layout="list" />
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		value: [Object]
	}
};
</script>
