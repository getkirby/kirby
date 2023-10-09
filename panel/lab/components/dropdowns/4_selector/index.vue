<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-selector-dropdown ref="selector" :options="options">
				<k-button
					@click="$refs.selector.toggle()"
					variant="filled"
					icon="check"
				>
					Select …
				</k-button>
			</k-selector-dropdown>
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
				},
				{
					text: "Photography",
				},
				{
					text: "Architecture",
				},
			];
		},
	},
};
</script>
