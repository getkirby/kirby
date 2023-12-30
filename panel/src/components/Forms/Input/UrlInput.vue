<template>
	<k-string-input
		v-bind="$props"
		type="url"
		class="k-url-input"
		@input="$emit('input', $event)"
		@invalid="($invalid, $v) => $emit('invalid', $invalid, $v)"
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
 * @example <k-input :value="url" @input="url = $event" name="url" type="url" />
 */
export default {
	mixins: [StringInput]
};
</script>
