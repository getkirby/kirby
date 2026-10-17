<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-table :columns="columns" :options="options" :rows="rows"></k-table>
		</k-lab-example>
		<k-lab-example label="Responsive: false">
			<k-table
				:columns="columns"
				:options="options"
				:responsive="false"
				:rows="rows"
			></k-table>
		</k-lab-example>
		<k-lab-example label="Sortable">
			<k-table
				:columns="columns"
				:options="options"
				:rows="rows"
				:sortable="true"
			></k-table>
		</k-lab-example>
		<k-lab-example label="Sortable columns">
			<k-table
				:columns="sortableColumns"
				:options="options"
				:rows="sortedRows"
				@header="onHeader"
			></k-table>
		</k-lab-example>
		<k-lab-example label="Selecting">
			<k-table
				:columns="columns"
				:options="options"
				:selecting="true"
				:rows="rows"
			></k-table>
		</k-lab-example>
		<k-lab-example label="Custom index (starting at 6789)">
			<k-table
				:columns="columns"
				:index="6789"
				:options="options"
				:rows="rows"
				:sortable="true"
			></k-table>
		</k-lab-example>
		<k-lab-example label="Theme: skeleton">
			<k-table :columns="columns" :rows="skeleton"></k-table>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			sortBy: null
		};
	},
	computed: {
		columns() {
			return {
				title: {
					label: "Title",
					mobile: true
				},
				date: {
					label: "Date"
				}
			};
		},
		options() {
			return [
				{
					text: "Edit",
					icon: "edit"
				},
				{
					text: "Duplicate",
					icon: "copy"
				},
				"-",
				{
					text: "Delete",
					icon: "trash"
				}
			];
		},
		sortableColumns() {
			const [field, direction] = this.sortBy?.split(" ") ?? [];

			return Object.fromEntries(
				Object.entries(this.columns).map(([name, column]) => [
					name,
					{ ...column, sorted: name === field ? direction : false }
				])
			);
		},
		sortedRows() {
			if (this.sortBy === null) {
				return this.rows;
			}

			return this.$helper.array.sortBy([...this.rows], this.sortBy);
		},
		rows() {
			return [
				{
					title: "Title 1",
					date: "2020-01-01"
				},
				{
					title: "Title 2",
					date: "2020-01-02"
				},
				{
					title: "Title 3",
					date: "2020-01-03",
					selectable: false
				}
			];
		},
		skeleton() {
			return [
				{ id: "skeleton-1", theme: "skeleton" },
				{ id: "skeleton-2", theme: "skeleton" }
			];
		}
	},
	methods: {
		onHeader({ columnIndex }) {
			const ascending = this.sortBy === columnIndex + " asc";
			this.sortBy = columnIndex + (ascending ? " desc" : " asc");
		}
	}
};
</script>
