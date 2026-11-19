<template>
	<k-string-input
		v-bind="$props"
		:class="['k-password-input', $attrs.class]"
		:style="$attrs.style"
		type="password"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "new-password"
		}
	}
};

/**
 * @example
 * <k-input :value="password" @input="password = $event" name="password" type="password" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [StringInput, props],
	emits: ["input"]
};
</script>
