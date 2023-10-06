<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button v-for="theme in themes" :key="theme" :theme="theme" icon="edit">
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: dimmed">
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="dimmed"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: filled">
			<k-button
				v-for="theme in themes"
				:key="theme"
				:theme="theme"
				variant="filled"
				icon="edit"
			>
				Button
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		themes: Array,
	},
};
</script>
