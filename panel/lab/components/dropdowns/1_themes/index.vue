<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="dark">
			<k-button
				:dropdown="true"
				variant="filled"
				@click="$refs.dropdownA.toggle()"
			>
				Dropdown
			</k-button>
			<k-dropdown ref="dropdownA" :options="options" />
		</k-lab-example>
		<k-lab-example :flex="true" label="light">
			<k-button
				:dropdown="true"
				variant="filled"
				@click="$refs.dropdownB.toggle()"
			>
				Dropdown
			</k-button>
			<k-dropdown ref="dropdownB" :options="options" theme="light" />
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
					icon: "edit"
				},
				{
					text: "Duplicate",
					icon: "copy"
				},
				"-",
				{
					text: "Active item",
					icon: "heart",
					current: true
				},
				"-",
				{
					text: "Delete",
					icon: "trash"
				}
			];
		}
	}
};
</script>
