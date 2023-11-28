<template>
	<k-lab-examples>
		<k-lab-example label="empty">
			<k-collection :empty="empty" />
		</k-lab-example>
		<k-lab-example label="cards">
			<k-collection :items="items" :empty="empty" layout="cardlets" />
		</k-lab-example>
		<k-lab-example label="pagination">
			<k-collection
				:items="items.slice(0, 8)"
				:empty="empty"
				:pagination="pagination"
				layout="cardlets"
			/>
		</k-lab-example>
		<k-lab-example label="help">
			<k-collection
				:items="items.slice(0, 8)"
				:empty="empty"
				:pagination="pagination"
				:help="help"
				layout="cardlets"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		empty: Object,
		help: String,
		items: Array,
		pagination: Object
	}
};
</script>
