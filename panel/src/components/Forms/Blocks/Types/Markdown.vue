<template>
	<k-input
		ref="input"
		:buttons="false"
		:placeholder="placeholder"
		:spellcheck="false"
		:value="content.text"
		class="k-block-type-markdown-input"
		font="monospace"
		type="textarea"
		@input="update({ text: $event })"
	/>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeMarkdown
 */
export default {
	extends: Block,
	computed: {
		placeholder() {
			return this.field("text", {}).placeholder;
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
.k-block-type-markdown-input {
	--input-color-back: var(--color-light);
	--input-color-border: none;
	--input-outline-focus: none;
	--input-padding-multiline: var(--spacing-3);
}
</style>
