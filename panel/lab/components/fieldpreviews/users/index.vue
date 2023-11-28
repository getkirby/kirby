<template>
	<k-lab-examples>
		<k-lab-field-preview-example :value="users" label="Default" type="users" />
		<k-lab-field-preview-example label="Empty" type="users" />
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		users() {
			return [
				{
					image: { icon: "user", back: "black", color: "white" },
					text: "marge@getkirby.com",
					link: "/",
				},
				{
					image: { icon: "user", back: "black", color: "white" },
					text: "maggie@getkirby.com",
					link: "/",
				},
			];
		},
	},
};
</script>
