<template>
	<k-box
		v-if="deprecated?.length"
		icon="protected"
		theme="warning"
		class="k-lab-docs-deprecated"
	>
		<k-text :html="'<strong>Deprecated:</strong> ' + deprecated" />
	</k-box>
</template>

<script>
export default {
	props: {
		deprecated: {
			type: String
		}
	}
};
</script>

<style>
.k-table .k-lab-docs-deprecated {
	--box-height: var(--height-xs);
	--text-font-size: var(--text-xs);
}
</style>
