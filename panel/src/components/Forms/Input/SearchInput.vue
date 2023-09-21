<template>
	<k-string-input
		v-bind="$props"
		type="search"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		placeholder: {
			default: () => window.panel.$t("search") + " …"
		}
	}
};

/**
 * @example <k-search-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>
