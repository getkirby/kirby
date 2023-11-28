<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>font-family: var(--font-mono)</code>
		</k-text>

		<k-lab-example
			v-for="family in families"
			:key="family"
			:label="`--font-${family}`"
			:code="false"
		>
			<p
				v-for="size in sizes"
				:key="size"
				:style="{
					'font-family': `var(--font-${family})`,
					'font-size': `var(--text-${size})`
				}"
			>
				The quick brown fox jumps over the lazy dog
			</p>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		families: Array,
		sizes: Array
	}
};
</script>

<style>
.k-lab-example p + p {
	margin-top: var(--spacing-3);
}
</style>
