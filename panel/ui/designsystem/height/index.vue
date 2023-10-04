<template>
	<k-ui-examples>
		<k-ui-example label="heights">
			<k-box
				v-for="height in heights"
				:key="height"
				:label="`height-${height}`"
				:style="`--box-height: var(--height-${height}); height: var(--box-height); font-size: var(--text-xs); font-family: var(--font-mono)`"
				theme="white"
			>
				height-{{ height }}
			</k-box>
		</k-ui-example>
		<k-ui-example :flex="true" label="comparison">
			<k-button variant="filled" size="lg">Button</k-button>
			<k-box height="var(--height-lg)" theme="white">Box</k-box>
			<k-inputbox>
				<input type="text" style="padding-inline: var(--input-padding)" />
			</k-inputbox>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		heights: Array,
	},
};
</script>

<style>
.k-ui-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
