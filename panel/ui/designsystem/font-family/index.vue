<template>
	<k-ui-examples>
		<k-ui-example
			v-for="family in families"
			:key="family"
			:label="`font-${family}`"
		>
			<p
				v-for="size in sizes"
				:key="size"
				:style="`font-family: var(--font-${family}); font-size: var(--text-${size})`"
			>
				The quick brown fox jumps over the lazy dog
			</p>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		families: Array,
		sizes: Array,
	},
};
</script>

<style>
.k-ui-example p + p {
	margin-top: var(--spacing-3);
}
</style>
