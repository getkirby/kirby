<template>
	<k-panel-inside class="k-ui-index-view">
		<k-header>UI</k-header>
		<k-collection :items="examples" />
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		examples: Array,
	},
};
</script>

<style>
.k-ui-index-view .k-list-items {
	grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
}
</style>
