<template>
	<div class="k-multiselect-input">
		<k-tags
			ref="tags"
			v-bind="$props"
			@input="$emit('input', $event)"
			@click.native.stop
		/>
	</div>
</template>

<script>
import TagsInput from "./TagsInput.vue";

export default {
	extends: TagsInput,
	inheritAttrs: false
};
</script>

<style>
.k-multiselect-input {
	padding: var(--tags-gap);
}
</style>
