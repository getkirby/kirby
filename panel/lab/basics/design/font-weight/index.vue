<template>
	<k-lab-examples>
		<k-lab-example
			v-for="weight in weights"
			:key="weight"
			:label="`--font-${weight}`"
			:code="false"
		>
			<p :style="`font-weight: var(--font-${weight})`">
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
