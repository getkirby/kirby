<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-breadcrumb :crumbs="crumbs" />
		</k-ui-example>
		<k-ui-example label="Too long">
			<k-breadcrumb :crumbs="tooLong" />
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		crumbs() {
			return [
				{
					icon: "home",
					label: "Site",
				},
				{
					label: "Blog",
				},
				{
					label: "Exploring the universe",
				},
			];
		},
		tooLong() {
			return [
				{
					icon: "home",
					label: "Site",
				},
				{
					label: "Our amazing blog",
				},
				{
					label: "Exploring the universe from one end to the other",
				},
				{
					label: "some-super-long-filename-that-might-cause-problems.jpg",
				},
			];
		},
	},
};
</script>
