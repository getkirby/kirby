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
				:selectable="true"
				layout="table"
				@select="onSelect"
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
		}
	},
	methods: {
		onSelect(item, index) {
			if (this.selected.includes(index)) {
				this.selected = this.selected.filter((i) => i !== index);
			} else {
				this.selected.push(index);
			}
		}
	}
};
</script>
