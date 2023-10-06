<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			:value="bubbles"
			label="Default"
			type="bubbles"
		/>
		<k-lab-field-preview-example label="Empty" type="bubbles" />
	</k-lab-examples>
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
