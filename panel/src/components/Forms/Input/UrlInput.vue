<template>
	<k-string-input
		v-bind="$props"
		class="k-url-input"
		type="url"
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
			default: "url"
		},
		placeholder: {
			type: String,
			default: () => window.panel.$t("url.placeholder")
		}
	}
};

/**
 * @example <k-url-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [StringInput]
};
</script>
