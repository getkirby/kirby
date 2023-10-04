<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="dark">
			<k-button
				:dropdown="true"
				variant="filled"
				@click="$refs.dropdownA.toggle()"
			>
				Dropdown
			</k-button>
			<k-dropdown-content ref="dropdownA" :options="options" />
		</k-ui-example>
		<k-ui-example :flex="true" label="light">
			<k-button
				:dropdown="true"
				variant="filled"
				@click="$refs.dropdownB.toggle()"
			>
				Dropdown
			</k-button>
			<k-dropdown-content ref="dropdownB" :options="options" theme="light" />
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
