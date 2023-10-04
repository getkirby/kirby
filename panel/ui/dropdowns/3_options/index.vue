<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="multiple options">
			<k-options-dropdown :options="options" variant="filled" />
		</k-ui-example>
		<k-ui-example :flex="true" label="single options">
			<k-options-dropdown :options="options.slice(0, 1)" variant="filled" />
		</k-ui-example>
	</k-ui-examples>
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
