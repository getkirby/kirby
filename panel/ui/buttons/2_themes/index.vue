<template>
	<k-ui-examples>
		<k-ui-example :flex="true" label="default">
			<k-button v-for="theme in themes" :key="theme" :theme="theme" icon="edit">
				Button
			</k-button>
		</k-ui-example>
		<k-ui-example :flex="true" label="variant: dimmed">
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="dimmed"
				icon="edit"
			>
				Button
			</k-button>
		</k-ui-example>
		<k-ui-example :flex="true" label="variant: filled">
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="filled"
				icon="edit"
			>
				Button
			</k-button>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		themes: Array,
	},
};
</script>
