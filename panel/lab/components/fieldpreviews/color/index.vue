<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			value="rgb(209, 232, 163)"
			label="Default"
			type="color"
		/>
		<k-lab-field-preview-example
			:field="field"
			value="rgb(209, 232, 163)"
			label="Default"
			type="color"
		/>
		<k-lab-field-preview-example :field="field" label="Empty" type="color" />
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		field() {
			return {
				alpha: false,
				format: "rgb",
				options: [{ text: "Mint", value: "rgb(209, 232, 163)" }],
			};
		},
	},
};
</script>
