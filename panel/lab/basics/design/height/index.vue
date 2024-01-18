<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>height: var(--height-lg)</code>
		</k-text>

		<k-lab-example label="heights" :code="false">
			<k-box
				v-for="height in heights"
				:key="height"
				:style="{
					'--box-height': `var(--height-${height})`,
					height: 'var(--box-height)',
					'font-size': 'var(--text-xs)',
					'font-family': 'var(--font-mono)'
				}"
				theme="white"
			>
				--height-{{ height }}
			</k-box>
		</k-lab-example>
		<k-lab-example :flex="true" label="comparison">
			<k-button variant="filled" size="lg">Button</k-button>
			<k-box height="var(--height-lg)" theme="white">Box</k-box>
			<k-input>
				<input
					type="text"
					:style="{ 'padding-inline': 'var(--input-padding)', outline: 'none' }"
				/>
			</k-input>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		heights: Array
	}
};
</script>

<style>
.k-lab-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
