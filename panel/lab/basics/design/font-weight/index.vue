<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>font-weight: var(--font-bold)</code>
		</k-text>

		<k-lab-example
			v-for="weight in weights"
			:key="weight"
			:label="`--font-${weight}`"
			:code="false"
		>
			<p :style="{ 'font-weight': `var(--font-${weight})` }">
				The quick brown fox jumps over the lazy dog
			</p>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		weights: Array
	}
};
</script>
