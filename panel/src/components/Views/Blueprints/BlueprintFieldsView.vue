<template>
	<k-panel-inside class="k-blueprint-fields-view">
		<k-header>Fields</k-header>
		<k-table :columns="columns" :rows="rows" />
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		fields: Array
	},
	computed: {
		columns() {
			return {
				label: {
					label: "Label",
					type: "url"
				},
				name: {
					label: "Name",
					type: "tags"
				},
				type: {
					label: "Type",
					type: "tags"
				},
				required: {
					label: "Required",
					type: "toggle"
				}
			};
		},
		rows() {
			return this.fields;
		}
	}
};
</script>
