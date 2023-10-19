<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button
				icon="checklist"
				variant="filled"
				@click="$refs.picklist1.toggle()"
			>
				Select
			</k-button>
			<k-picklist-dropdown ref="picklist1" :options="options" />
		</k-lab-example>

		<k-lab-example :flex="true" label="create">
			<k-button
				icon="checklist"
				variant="filled"
				@click="$refs.picklist2.toggle()"
			>
				Select
			</k-button>
			<k-picklist-dropdown ref="picklist2" :create="true" :options="options" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		options() {
			return [
				{
					text: "Design",
					value: "design"
				},
				{
					text: "Photography",
					value: "photography"
				},
				{
					text: "Architecture",
					value: "architecture"
				}
			];
		}
	}
};
</script>
