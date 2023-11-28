<template>
	<k-lab-examples>
		<k-lab-example label="empty">
			<k-collection :empty="empty" />
		</k-lab-example>
		<k-lab-example label="list">
			<k-collection :items="items" :empty="empty" />
		</k-lab-example>
		<k-lab-example label="pagination">
			<k-collection
				:items="items.slice(0, 8)"
				:empty="empty"
				:pagination="pagination"
			/>
		</k-lab-example>
		<k-lab-example label="help">
			<k-collection
				:items="items.slice(0, 8)"
				:empty="empty"
				:pagination="pagination"
				:help="help"
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
