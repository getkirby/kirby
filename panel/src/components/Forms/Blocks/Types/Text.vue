<template>
	<component
		:is="component"
		ref="input"
		v-bind="textField"
		:disabled="disabled"
		:keys="keys"
		:value="content.text"
		class="k-block-type-text-input"
		@input="update({ text: $event })"
	/>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeText
 */
export default {
	extends: Block,
	emits: ["open", "split", "update"],
	computed: {
		component() {
			const component = "k-" + this.textField.type + "-input";

			if (this.$helper.isComponent(component)) {
				return component;
			}

			// fallback to writer
			return "k-writer-input";
		},
		isSplitable() {
			return (
				this.content.text.length > 0 &&
				this.$refs.input.isCursorAtStart === false &&
				this.$refs.input.isCursorAtEnd === false
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
			const contents = this.$refs.input.getSplitContent?.();

			if (contents) {
				if (this.textField.type === "writer") {
					contents[0] = contents[0].replace(/(<p><\/p>)$/, "");
					contents[1] = contents[1].replace(/^(<p><\/p>)/, "");
				}

				this.$emit(
					"split",
					contents.map((content) => ({ text: content }))
				);
			}
		}
	}
};
</script>

<style>
.k-block-type-text-input {
	line-height: 1.5;
	height: 100%;
}
.k-block-container.k-block-container-type-text {
	padding: 0;
}
.k-block-type-text-input.k-writer-input[data-toolbar-inline="true"] {
	padding: var(--spacing-3);
}
.k-block-type-text-input.k-writer-input:not([data-toolbar-inline="true"])
	> .ProseMirror,
.k-block-type-text-input.k-writer-input:not(
		[data-toolbar-inline="true"]
	)[data-placeholder][data-empty="true"]:before {
	padding: var(--spacing-3) var(--spacing-6);
}

.k-block-type-text-input.k-textarea-input .k-textarea-input-native {
	padding: var(--input-padding-multiline);
}
</style>
