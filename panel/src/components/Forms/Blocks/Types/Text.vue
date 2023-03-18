<template>
	<component
		:is="component"
		ref="input"
		v-bind="textField"
		:keys="keys"
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
		isSplitable() {
			return (
				this.content.text.length > 0 &&
				this.editor().selectionIsAtStart === false &&
				this.editor().selectionIsAtEnd === false
			);
		},
		keys() {
			const keys = {
				"Mod-Enter": this.split
			};

			if (this.textField.inline === true) {
				keys.Enter = this.split;
			}

			return keys;
		},
		textField() {
			return this.field("text", {});
		}
	},
	methods: {
		editor() {
			return this.$refs.input.editor;
		},
		focus() {
			this.$refs.input.focus();
		},
		merge(blocks) {
			this.update({
				text: blocks
					.map((block) => block.content.text)
					.join(this.textField.inline ? " " : "")
			});
		},
		split() {
			const contents = this.editor().getHTMLStartToSelectionToEnd();
			this.$emit("split", [
				{ text: contents[0].replace(/(<p><\/p>)$/, "") },
				{ text: contents[1].replace(/^(<p><\/p>)/, "") }
			]);
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
