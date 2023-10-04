<template>
	<k-ui-examples>
		<k-ui-example label="shadows">
			<k-box
				v-for="shadow in shadows"
				:key="shadow"
				:label="`shadow-${shadow}`"
				:style="`box-shadow: var(--shadow-${shadow}); font-size: var(--text-xs); font-family: var(--font-mono)`"
				theme="white"
			>
				shadow-{{ shadow }}
			</k-box>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		shadows: Array,
	},
};
</script>

<style>
.k-ui-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
