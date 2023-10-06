<template>
	<k-lab-examples>
		<k-lab-example label="spacings">
			<k-box
				v-for="spacing in spacings"
				:key="spacing"
				:label="`spacing-${spacing}`"
				:style="`margin-bottom: var(--spacing-${spacing}); font-size: var(--text-xs); font-family: var(--font-mono)`"
				theme="white"
			>
				spacing-{{ spacing }}
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		spacings: Array,
	},
};
</script>
