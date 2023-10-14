<template>
	<k-string-input
		v-bind="$props"
		class="k-search-input"
		type="search"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			default: "off"
		},
		placeholder: {
			default: () => window.panel.$t("search") + " …",
			type: String
		},
		spellcheck: {
			default: false
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-search-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props]
};
</script>
