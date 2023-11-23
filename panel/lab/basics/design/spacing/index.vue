<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>margin-bottom: var(--spacing-6)</code>
		</k-text>

		<k-lab-example label="spacings" :code="false">
			<k-box
				v-for="spacing in spacings"
				:key="spacing"
				:style="{
					'margin-bottom': `var(--spacing-${spacing})`,
					'font-size': 'var(--text-xs)',
					'font-family': 'var(--font-mono)'
				}"
				theme="white"
			>
				--spacing-{{ spacing }}
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		spacings: Array
	}
};
</script>
