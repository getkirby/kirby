<template>
	<k-ui-examples>
		<k-ui-field-preview-example :value="image" label="Default" type="image" />
		<k-ui-field-preview-example label="Empty" type="image" />
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		image() {
			return {
				src: "https://picsum.photos/200/300",
			};
		},
	},
};
</script>
