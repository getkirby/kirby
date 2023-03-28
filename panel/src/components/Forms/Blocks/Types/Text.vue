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
	font-size: var(--text-md);
	line-height: 1.5em;
	height: 100%;
}
.k-block-type-text,
.k-block-container-type-text,
.k-block-type-text .k-writer .ProseMirror {
	height: 100%;
}
</style>
