<template>
	<k-text class="k-lab-docs-types">
		<code v-for="type in types" :key="type" :data-type="type">
			{{ type }}
		</code>
	</k-text>
</template>

<script>
export default {
	props: {
		types: {
			type: Array,
			default: () => []
		}
	}
};
</script>

<style>
.k-lab-docs-types {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}
</style>
