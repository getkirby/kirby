<template>
	<component
		:is="component"
		ref="input"
		v-bind="textField"
		:value="content.text"
		class="k-block-type-text-input"
		@input="update({ text: $event })"
	/>
</template>

<script>
/**
 * @displayName BlockTypeText
 * @internal
 */
export default {
	computed: {
		component() {
			const component = "k-" + this.textField.type + "-input";

			if (this.$helper.isComponent(component)) {
				return component;
			}

			// fallback to writer
			return "k-writer";
		},
		textField() {
			return this.field("text", {});
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
.k-block-type-text-input {
	font-size: var(--text-base);
	line-height: 1.5em;
	height: 100%;
}
.k-block-type-text,
.k-block-container-type-text,
.k-block-type-text .k-writer .ProseMirror {
	height: 100%;
}
</style>
