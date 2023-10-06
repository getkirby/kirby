<template>
	<k-lab-examples>
		<k-lab-field-preview-example :value="image" label="Default" type="image" />
		<k-lab-field-preview-example label="Empty" type="image" />
	</k-lab-examples>
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
