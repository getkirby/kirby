<template>
	<k-lab-examples>
		<k-lab-example label="table">
			<k-items
				:columns="columns"
				:items="items"
				:sortable="true"
				layout="table"
			/>
		</k-lab-example>
		<k-lab-example label="Selectable">
			<k-items
				:columns="columns"
				:items="selectableItems"
				:selecting="true"
				:selected="selected"
				layout="table"
				@select="selected = $event"
			/>
			<br />
			<k-code>Selected: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
		<k-lab-example label="Selectable: single">
			<k-items
				:columns="columns"
				:items="selectableItems"
				:selecting="true"
				selectmode="single"
				:selected="selected"
				layout="table"
				@select="selected = $event"
			/>
			<br />
			<k-code>Selected: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	props: {
		items: Array
	},
	data() {
		return {
			selected: []
		};
	},
	computed: {
		columns() {
			return {
				image: {
					label: "",
					type: "image",
					width: "var(--table-row-height)"
				},
				text: {
					label: "Text",
					type: "text"
				},
				info: {
					label: "Info",
					type: "text"
				}
			};
		},
		selectableItems() {
			return this.items.map((item) => {
				return {
					...item,
					selectable: true
				};
			});
		},
		selectedIds() {
			return this.selected.map((item) => item.id).join(", ");
		}
	}
};
</script>
