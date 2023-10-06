<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>color: var(--color-gray-100)</code>
		</k-text>

		<k-lab-example label="colors" :code="false">
			<k-grid
				v-for="name in names"
				:key="name"
				style="--columns: 9; gap: var(--spacing-3)"
			>
				<k-box
					v-for="(step, index) in steps"
					:key="step"
					:style="`background: var(--color-${name}-${step}); color: var(--color-${name}-${colors[step]})`"
				>
					{{ name }}-{{ step }}
				</k-box>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		names: Array,
		steps: Array
	},
	computed: {
		colors() {
			return {
				100: 800,
				200: 800,
				300: 900,
				400: 1000,
				500: 1000,
				600: 1000,
				700: 100,
				800: 200,
				900: 200
			};
		}
	}
};
</script>

<style>
.k-lab-example .k-grid + .k-grid {
	margin-top: var(--spacing-3);
}
.k-lab-example .k-box {
	padding: var(--spacing-3);
	font-size: var(--text-xs);
	font-family: var(--font-mono);
	border-radius: var(--rounded);
	overflow: hidden;
}
</style>
