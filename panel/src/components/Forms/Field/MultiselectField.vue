<template>
	<k-field
		v-bind="$props"
		:input="uid"
		:counter="counterOptions"
		class="k-multiselect-field"
	>
		<k-empty v-if="hasNoOptions" :icon="icon" :text="$t('options.none')" />
		<k-input
			v-else
			v-bind="$props"
			:id="uid"
			ref="input"
			theme="field"
			type="multiselect"
			v-on="$listeners"
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
	inheritAttrs: false
};
</script>
