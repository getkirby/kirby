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
import TagsInput, { props as TagsInputProps } from "./TagsInput.vue";

export const props = {
	mixins: [TagsInputProps],
	props: {
		accept: {
			default: "string",
			type: String
		}
	}
};

export default {
	extends: TagsInput,
	mixins: [props]
};
</script>

<style>
.k-multiselect-input {
	padding: var(--tags-gap);
}
</style>
