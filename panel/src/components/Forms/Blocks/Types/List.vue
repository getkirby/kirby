<template>
	<k-input
		ref="input"
		:marks="marks"
		:value="content.text"
		class="k-block-type-list-input"
		type="list"
		@input="update({ text: $event })"
		@keydown.native.backspace.exact="onBackspace"
	/>
</template>

<script>
import BlockType from "../BlockType.vue";

/**
 * @displayName BlockTypeList
 * @internal
 */
export default {
	extends: BlockType,
	computed: {
		marks() {
			return this.field("text", {}).marks;
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
