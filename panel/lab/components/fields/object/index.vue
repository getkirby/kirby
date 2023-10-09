<template>
	<k-lab-examples>
		<k-lab-example label="No fields">
			<k-object-field label="Object" />
		</k-lab-example>
		<k-lab-example label="Default">
			<k-object-field :fields="fields" label="Object" />
		</k-lab-example>
	</k-lab-examples>
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
