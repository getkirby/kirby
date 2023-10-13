<template>
	<k-panel-inside class="k-lab-index-view">
		<k-header>Lab</k-header>

		<k-section
			v-for="category in categories"
			:key="category.name"
			:headline="category.name"
		>
			<k-collection
				:items="category.examples"
				:empty="{
					icon: category.icon,
					text: 'Add examples to ' + category.path
				}"
			/>
		</k-section>

		<k-section headline="Docs">
			<k-collection :items="docs" />
		</k-section>
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		categories: Array,
		docs: Array
	}
};
</script>

<style>
.k-lab-index-view .k-list-items {
	grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
}
</style>
