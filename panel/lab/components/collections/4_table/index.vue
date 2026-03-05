<template>
	<k-lab-examples>
		<k-lab-example label="table">
			<k-collection
				:columns="columns"
				:items="tableItems"
				:empty="empty"
				layout="table"
			/>
		</k-lab-example>

		<k-lab-example label="table pagination index">
			<k-collection
				:columns="columns"
				:items="tableItems"
				:empty="empty"
				layout="table"
				:pagination="tablePagination"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		empty: Object,
		items: Array,
		pagination: Object
	},
	computed: {
		columns() {
			return {
				title: {
					label: "Title"
				},
				info: {
					label: "Info"
				}
			};
		},
		tableItems() {
			return this.items.slice(0, 8).map((item, index) => ({
				title: item.text ?? `Item ${index + 1}`,
				info: item.info ?? "Some info text"
			}));
		},
		tablePagination() {
			return {
				...this.pagination,
				offset: 16
			};
		}
	}
};
</script>
