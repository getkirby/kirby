<template>
	<k-box
		v-if="text?.length"
		:icon="icon"
		theme="warning"
		class="k-lab-docs-warning"
	>
		<k-text :html="'<strong>' + title + ':</strong> ' + text" />
	</k-box>
</template>

<script>
export default {
	props: {
		icon: {
			type: String,
			default: "protected"
		},
		title: String,
		text: String
	}
};
</script>

<style>
.k-table .k-lab-docs-warning {
	--box-height: var(--height-xs);
	--text-font-size: var(--text-xs);
}
</style>
