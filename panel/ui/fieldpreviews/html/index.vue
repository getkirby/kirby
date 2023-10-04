<template>
	<k-ui-examples>
		<k-ui-field-preview-example
			value="<b>This</b> is some <code>HTML</code>"
			label="Default"
			type="html"
		/>
		<k-ui-field-preview-example :value="html" label="Multiline" type="html" />
		<k-ui-field-preview-example label="Empty" type="html" />
	</k-ui-examples>
</template>

<script>
export default {
	computed: {
		html() {
			return `
				<p>First <strong>paragraph</strong> with some more <code>HTML</code> to fill the space</p>
				<p>One more paragraph with <a href="">a link</a></p>
			`;
		},
	},
};
</script>
