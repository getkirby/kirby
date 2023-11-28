<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>z-index: var(--z-toolbar)</code>
		</k-text>

		<k-lab-example label="z-index" :code="false">
			<div>
				<k-box
					v-for="(index, x) in z"
					:key="index"
					:style="{
						'z-index': `var(--z-${index})`,
						'font-size': 'var(--text-xs)',
						'font-family': 'var(--font-mono)',
						'inset-block-start': x * 24 + 'px',
						'inset-inline-start': x * 24 + 'px'
					}"
					theme="white"
				>
					--z-{{ index }}
				</k-box>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		z: Array
	}
};
</script>

<style>
.k-lab-example-canvas > div {
	position: relative;
}
.k-lab-example .k-box {
	position: absolute;
	width: auto;
	box-shadow: var(--shadow-xl);
}
</style>
