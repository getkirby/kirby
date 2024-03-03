<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-lab-table-cell>
				<!-- @code -->
				<k-html-field-preview value="<b>This</b> is some <code>HTML</code>" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Multiline">
			<k-lab-table-cell>
				<!-- @code -->
				<k-html-field-preview :value="html" />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>

		<k-lab-example label="Empty">
			<k-lab-table-cell>
				<!-- @code -->
				<k-html-field-preview />
				<!-- @code-end -->
			</k-lab-table-cell>
		</k-lab-example>
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
		}
	}
};
</script>
