<template>
	<k-string-input
		v-bind="$props"
		:class="['k-search-input', $attrs.class]"
		:spellcheck="false"
		:style="$attrs.style"
		autocomplete="off"
		type="search"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		// unset props
		autocomplete: null,
		pattern: null,
		spellcheck: null,

		placeholder: {
			default: () => window.panel.t("search") + " …",
			type: String
		}
	}
};

/**
 * @example
 * <k-search-input :value="value" @input="value = $event" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 */
export default {
	mixins: [StringInput, props],
	emits: ["input"]
};
</script>
