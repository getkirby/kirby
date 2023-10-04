<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			:value="bubbles"
			label="Default"
			type="bubbles"
		/>
		<k-ui-field-preview-example label="Empty" type="bubbles" />
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		bubbles() {
			return [
				{
					text: "A",
				},
				{
					text: "B",
				},
			];
		},
	},
};
</script>
