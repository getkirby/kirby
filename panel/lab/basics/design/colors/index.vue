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
				style="--columns: 20; gap: var(--spacing-3)"
			>
				<k-box style="--span: 2"> {{ name }}- </k-box>
				<k-box
					v-for="(step, index) in steps"
					:key="step"
					:style="{
						background: `var(--color-${name}-${step})`,
						color: `var(--color-${name}-${colors[step]})`
					}"
				>
					{{ step }}
				</k-box>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="theme" :code="false">
			<k-text>
				<p>In some situations, you might want to use different colors for light and dark mode of the Panel. For this, you can use the <code>light-dark()</code> CSS function which will pick the respective color/value based on the current theme:</p>
				<k-code language="css">background: light-dark(var(--color-gray-250), var(--color-gray-850));</k-code>
			</k-text>
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
				150: 800,
				200: 800,
				250: 800,
				300: 900,
				350: 900,
				400: 1000,
				450: 1000,
				500: 1000,
				550: 100,
				600: 100,
				650: 100,
				700: 100,
				750: 100,
				800: 200,
				850: 200,
				900: 200,
				950: 200
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
