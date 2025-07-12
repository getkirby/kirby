<template>
	<k-field
		v-bind="$props"
		:class="['k-multiselect-field', $attrs.class]"
		:counter="counterOptions"
		:input="id"
		:style="$attrs.style"
	>
		<k-empty v-if="hasNoOptions" :icon="icon" :text="$t('options.none')" />
		<k-input
			v-else
			v-bind="$props"
			ref="input"
			type="multiselect"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import TagsField from "./TagsField.vue";

/**
 * Have a look at `<k-tags-field>`.
 */
export default {
	extends: TagsField,
	inheritAttrs: false,
	emits: ["input"]
};
</script>
