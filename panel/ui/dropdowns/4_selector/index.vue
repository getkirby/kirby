<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
			<k-selector-dropdown ref="selector" :options="options">
				<k-button
					@click="$refs.selector.toggle()"
					variant="filled"
					icon="check"
				>
					Select …
				</k-button>
			</k-selector-dropdown>
		</k-ui-example>
	</k-ui-examples>
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
