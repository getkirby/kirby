<template>
	<k-lab-examples>
		<k-lab-example label="cardlets">
			<k-items :items="items" layout="cardlets" />
		</k-lab-example>
		<k-lab-example label="Selectable">
			<k-items
				:items="selectableItems"
				:selectable="true"
				layout="cardlets"
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
