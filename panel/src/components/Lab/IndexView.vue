<template>
	<k-panel-inside class="k-lab-index-view">
		<k-header>Lab</k-header>

		<k-section
			v-for="category in examples"
			:key="category.name"
			:headline="category.name"
		>
			<k-collection :items="category.examples" />
		</k-section>
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		examples: Array
	}
};
</script>

<style>
.k-lab-index-view .k-list-items {
	grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
}
</style>
