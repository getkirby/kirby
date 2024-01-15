<template>
	<k-input
		ref="input"
		:disabled="disabled"
		:keys="keys"
		:marks="marks"
		:value="content.text"
		class="k-block-type-list-input"
		type="list"
		@input="update({ text: $event })"
	/>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeList
 */
export default {
	extends: Block,
	emits: ["open", "split", "update"],
	computed: {
		isSplitable() {
			return (
				this.content.text.length > 0 &&
				this.input().isCursorAtStart === false &&
				this.input().isCursorAtEnd === false
			);
		},
		keys() {
			return {
				"Mod-Enter": this.split
			};
		},
		marks() {
			return this.field("text", {}).marks;
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		input() {
			return this.$refs.input.$refs.input.$refs.input;
		},
		merge(blocks) {
			this.update({
				text: blocks
					.map((block) => block.content.text)
					.join("")
					.replaceAll("</ul><ul>", "")
			});
		},
		split() {
			const contents = this.input().getSplitContent?.();

			if (contents) {
				this.$emit("split", [
					{ text: contents[0].replace(/(<li><p><\/p><\/li><\/ul>)$/, "</ul>") },
					{ text: contents[1].replace(/^(<ul><li><p><\/p><\/li>)/, "<ul>") }
				]);
			}
		}
	}
};
</script>

<style>
.k-block-type-list-input {
	--input-color-back: transparent;
	--input-color-border: none;
	--input-outline-focus: none;
}
</style>
