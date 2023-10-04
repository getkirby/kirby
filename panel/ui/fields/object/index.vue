<template>
	<k-ui-examples>
		<k-ui-example label="No fields">
			<k-object-field label="Object" />
		</k-ui-example>
		<k-ui-example label="Default">
			<k-object-field :fields="fields" label="Object" />
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		fields() {
			return [
				{
					label: "Name",
					name: "name",
					type: "text",
				},
			];
		},
	},
};
</script>
