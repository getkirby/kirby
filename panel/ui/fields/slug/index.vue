<template>
	<k-ui-field-examples type="slug">
		<k-ui-example label="Wizard">
			<k-slug-field
				:formData="formData"
				:value="value"
				:wizard="wizard"
				label="Slug"
				@input="value = $event"
			/>
		</k-ui-example>
	</k-ui-field-examples>
</template>

<script>
export default {
	data() {
		return {
			value: null,
		};
	},
	computed: {
		formData() {
			return {
				title: "This is a custom slug",
			};
		},
		wizard() {
			return {
				text: "Create slug",
				field: "title",
			};
		},
	},
};
</script>
