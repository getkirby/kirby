<template>
	<k-input
		ref="input"
		:keys="keys"
		:marks="marks"
		:value="content.text"
		class="k-block-type-list-input"
		type="list"
		@input="update({ text: $event })"
	/>
</template>

<script>
/**
 * @displayName BlockTypeList
 * @internal
 */
export default {
	computed: {
		isSplitable() {
			return (
				this.content.text.length > 0 &&
				this.editor().selectionIsAtStart === false &&
				this.editor().selectionIsAtEnd === false
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
		editor() {
			return this.$refs.input.$refs.input.$refs.input.editor;
		},
		focus() {
			this.$refs.input.focus();
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
			const contents = this.editor().getHTMLStartToSelectionToEnd();
			this.$emit("split", [
				{ text: contents[0].replace(/(<li><p><\/p><\/li><\/ul>)$/, "</ul>") },
				{ text: contents[1].replace(/^(<ul><li><p><\/p><\/li>)/, "<ul>") }
			]);
		}
	}
};
</script>
