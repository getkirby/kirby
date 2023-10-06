<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="multiple options">
			<k-options-dropdown :options="options" variant="filled" />
		</k-lab-example>
		<k-lab-example :flex="true" label="single options">
			<k-options-dropdown :options="options.slice(0, 1)" variant="filled" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		options() {
			return [
				{
					text: "Edit",
					icon: "edit",
				},
				{
					text: "Duplicate",
					icon: "copy",
				},
				"-",
				{
					text: "Delete",
					icon: "trash",
				},
			];
		},
	},
};
</script>
