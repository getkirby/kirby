<template>
	<k-lab-examples>
		<k-lab-field-preview-example
			value="<b>This</b> is some <code>HTML</code>"
			label="Default"
			type="html"
		/>
		<k-lab-field-preview-example :value="html" label="Multiline" type="html" />
		<k-lab-field-preview-example label="Empty" type="html" />
	</k-lab-examples>
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
