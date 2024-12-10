<template>
	<k-lab-examples>
		<k-lab-example label="List">
			<k-items :items="items" />
		</k-lab-example>
		<k-lab-example label="Selectable">
			<k-items :items="selectableItems" @select="onSelect" />
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
		onSelect(event, item, index) {
			if (event.target.checked) {
				this.selected.push(index);
			} else {
				this.selected = this.selected.filter((i) => i !== index);
			}
		}
	}
};
</script>
