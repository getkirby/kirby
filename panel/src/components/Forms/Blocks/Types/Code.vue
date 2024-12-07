<template>
	<div class="k-block-type-code-editor">
		<k-input
			ref="code"
			:buttons="false"
			:disabled="disabled"
			:placeholder="placeholder"
			:spellcheck="false"
			:value="content.code"
			font="monospace"
			type="textarea"
			@input="update({ code: $event })"
		/>
		<div v-if="languages.length" class="k-block-type-code-editor-language">
			<k-input
				ref="language"
				:disabled="disabled"
				:empty="false"
				:options="languages"
				:value="content.language"
				icon="code"
				type="select"
				@input="update({ language: $event })"
			/>
		</div>
	</div>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeCode
 */
export default {
	extends: Block,
	computed: {
		placeholder() {
			return this.field("code", {}).placeholder;
		},
		languages() {
			return this.field("language", { options: [] }).options;
		}
	},
	methods: {
		focus() {
			this.$refs.code.focus();
		}
	}
};
</script>

<style>
.k-block-type-code-editor {
	position: relative;
}
.k-block-type-code-editor .k-input {
	--input-color-border: none;
	--input-color-back: var(--color-black);
	--input-color-text: var(--color-white);
	--input-font-family: var(--font-mono);
	--input-outline-focus: none;
	--input-padding: var(--spacing-3);
	--input-padding-multiline: var(--input-padding);
}

.k-block-type-code-editor .k-input[data-type="textarea"] {
	white-space: pre-wrap;
}
.k-block-type-code-editor-language {
	--input-font-size: var(--text-xs);
	position: absolute;
	inset-inline-end: 0;
	bottom: 0;
}
.k-block-type-code-editor-language .k-input-element {
	padding-inline-start: 1.5rem;
}
.k-block-type-code-editor-language .k-input-icon {
	inset-inline-start: 0;
}
</style>
