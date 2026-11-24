<template>
	<k-lab-examples>
		<k-lab-example label="Cardlets">
			<k-items :items="items" layout="cardlets" />
		</k-lab-example>
		<k-lab-example label="Sortable">
			<k-items :items="items" :sortable="true" layout="cardlets" />
		</k-lab-example>
		<k-lab-example label="Selectable">
			<k-items
				:items="selectableItems"
				:selecting="true"
				:selected="selected"
				layout="cardlets"
				@select="selected = $event"
			/>
			<br />
			<k-code>Selected: {{ selected.join(", ") }}</k-code>
		</k-lab-example>
		<k-lab-example label="Selected">
			<k-items
				:items="selectableItems"
				:selecting="true"
				selectmode="single"
				:selected="selected"
				layout="cardlets"
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
		selectableItems() {
			return this.items.map((item) => {
				return {
					...item,
					selectable: true
				};
			});
		}
	}
};
</script>
