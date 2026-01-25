<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-table :columns="columns" :options="options" :rows="rows"></k-table>
		</k-lab-example>
		<k-lab-example label="Sortable">
			<k-table
				:columns="columns"
				:options="options"
				:rows="rows"
				:sortable="true"
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
	</k-lab-examples>
</template>

<script>
export default {
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
		}
	}
};
</script>
