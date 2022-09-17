<template>
	<k-block-title
		:content="content"
		:fieldset="fieldset"
		@dblclick="$emit('open')"
	/>
</template>

<script>
import BlockType from "../BlockType.vue";

/**
 * @displayName BlockTypeDefault
 * @internal
 */
export default {
	extends: BlockType
};
</script>

<style>
.k-block-type-default .k-block-title {
	line-height: 1.5em;
}
</style>
