<template>
	<k-ui-examples>
		<k-ui-example
			v-for="weight in weights"
			:key="weight"
			:label="`font-${weight}`"
		>
			<p :style="`font-weight: var(--font-${weight})`">
				The quick brown fox jumps over the lazy dog
			</p>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		weights: Array,
	},
};
</script>
