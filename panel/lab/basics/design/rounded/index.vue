<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>border-radius: var(--rounded-sm)</code>
		</k-text>

		<k-lab-example label="rounded" :code="false">
			<k-box
				v-for="radius in radii"
				:key="radius"
				:style="{
					'border-radius': `var(--rounded-${radius})`,
					'font-size': 'var(--text-xs)',
					'font-family': 'var(--font-mono)'
				}"
				theme="white"
			>
				--rounded-{{ radius }}
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		radii: Array
	}
};
</script>

<style>
.k-lab-example .k-box + .k-box {
	margin-top: var(--spacing-3);
}
</style>
