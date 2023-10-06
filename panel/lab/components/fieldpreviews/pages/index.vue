<template>
	<k-lab-examples>
		<k-lab-field-preview-example :value="pages" label="Default" type="pages" />
		<k-lab-field-preview-example label="Empty" type="pages" />
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		pages() {
			return [
				{
					image: { icon: "page", back: "black", color: "white" },
					text: "Projects",
					link: "/",
				},
				{
					image: { icon: "page", back: "black", color: "white" },
					text: "Blog",
					link: "/",
				},
			];
		},
	},
};
</script>
