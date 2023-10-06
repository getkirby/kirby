<template>
	<k-lab-examples>
		<k-lab-field-preview-example :value="files" label="Default" type="files" />
		<k-lab-field-preview-example label="Empty" type="files" />
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		files() {
			return [
				{
					image: { icon: "code", back: "black", color: "white" },
					filename: "test-1.js",
					link: "/",
				},
				{
					image: { icon: "code", back: "black", color: "white" },
					filename: "test-2.js",
					link: "/",
				},
			];
		},
	},
};
</script>
