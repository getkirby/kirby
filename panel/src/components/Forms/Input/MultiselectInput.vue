<template>
	<div class="k-multiselect-input">
		<k-tags v-bind="$props" @input="$emit('input', $event)" />
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
/* Field Theme */
.k-multiselect-input {
	padding: 0.25rem;
}
</style>
