<template>
	<div class="k-block-type-quote-editor">
		<k-writer-input
			ref="text"
			:inline="textField.inline ?? false"
			:marks="textField.marks"
			:placeholder="textField.placeholder"
			:value="content.text"
			class="k-block-type-quote-text"
			@input="update({ text: $event })"
		/>
		<k-writer-input
			ref="citation"
			:inline="citationField.inline ?? true"
			:marks="citationField.marks"
			:placeholder="citationField.placeholder"
			:value="content.citation"
			class="k-block-type-quote-citation"
			@input="update({ citation: $event })"
		/>
	</div>
</template>

<script>
/**
 * @displayName BlockTypeQuote
 * @internal
 */
export default {
	computed: {
		citationField() {
			return this.field("citation", {});
		},
		textField() {
			return this.field("text", {});
		}
	},
	methods: {
		focus() {
			this.$refs.text.focus();
		}
	}
};
</script>

<style>
.k-block-type-quote-editor {
	padding-inline-start: var(--spacing-3);
	border-inline-start: 2px solid var(--color-black);
}
.k-block-type-quote-text {
	font-size: var(--text-xl);
	margin-bottom: var(--spacing-1);
	line-height: 1.25em;
}
.k-block-type-quote-citation {
	font-style: italic;
	color: var(--color-text-dimmed);
}
</style>
