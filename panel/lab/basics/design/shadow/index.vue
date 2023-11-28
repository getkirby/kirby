<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>box-shadow: var(--shadow-lg)</code>
		</k-text>

		<k-lab-example label="shadows" :code="false">
			<k-box
				v-for="shadow in shadows"
				:key="shadow"
				:style="{
					'box-shadow': `var(--shadow-${shadow})`,
					'font-size': 'var(--text-xs)',
					'font-family': 'var(--font-mono)'
				}"
				theme="white"
			>
				--shadow-{{ shadow }}
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		shadows: Array
	}
};
</script>

<style>
.k-lab-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
