<template>
	<k-string-input
		v-bind="$props"
		:class="['k-text-input', $attrs.class]"
		type="text"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps]
};

/**
 * @example
 * <k-text-input :value="text" @input="text = $event" name="text" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [StringInput, props],
	emits: ["input"]
};
</script>
