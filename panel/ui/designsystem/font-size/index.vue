<template>
	<k-ui-examples>
		<k-ui-example v-for="size in sizes" :key="size" :label="`text-${size}`">
			<p :style="`font-size: var(--text-${size})`">
				The quick brown fox jumps over the lazy dog
			</p>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	props: {
		sizes: Array,
	},
};
</script>
