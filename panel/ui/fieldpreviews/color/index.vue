<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			value="rgb(209, 232, 163)"
			label="Default"
			type="color"
		/>
		<k-ui-field-preview-example
			:field="field"
			value="rgb(209, 232, 163)"
			label="Default"
			type="color"
		/>
		<k-ui-field-preview-example :field="field" label="Empty" type="color" />
	</k-ui-examples>
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
