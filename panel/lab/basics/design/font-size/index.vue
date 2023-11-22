<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>font-size: var(--text-sm)</code>
		</k-text>

		<k-lab-example
			v-for="size in sizes"
			:key="size"
			:label="`--text-${size}`"
			:code="false"
		>
			<p :style="{ 'font-size': `var(--text-${size})` }">
				The quick brown fox jumps over the lazy dog
			</p>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		sizes: Array
	}
};
</script>
