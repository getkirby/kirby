<template>
	<k-lab-examples>
		<k-lab-example label="rounded">
			<k-box
				v-for="radius in radii"
				:key="radius"
				:label="`rounded-${radius}`"
				:style="`border-radius: var(--rounded-${radius}); font-size: var(--text-xs); font-family: var(--font-mono)`"
				theme="white"
			>
				rounded-{{ radius }}
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		radii: Array,
	},
};
</script>

<style>
.k-lab-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
