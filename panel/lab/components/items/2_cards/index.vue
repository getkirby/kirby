<template>
	<k-lab-examples>
		<k-lab-example label="Cards">
			<k-items :items="items" layout="cards" />
		</k-lab-example>
		<k-lab-example label="Sortable">
			<k-items :items="items" :sortable="true" layout="cards" />
		</k-lab-example>
		<k-lab-example label="Selectable">
			<k-items
				:items="selectableItems"
				:selecting="true"
				:selected="selected"
				layout="cards"
				@selected="selected = $event"
			/>
			<br />
			<k-code>Selected: {{ selectedIds }}</k-code>
		</k-lab-example>
		<k-lab-example label="Selected">
			<k-items
				:items="selectableItems"
				selecting="single"
				:selected="selected"
				layout="cards"
				@selected="selected = $event"
			/>
			<br />
			<k-code>Selected: {{ selectedIds }}</k-code>
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
