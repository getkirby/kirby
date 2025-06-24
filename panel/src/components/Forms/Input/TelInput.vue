<template>
	<k-string-input
		v-bind="$props"
		type="tel"
		class="k-tel-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			default: "tel"
		},
		placeholder: {
			default: () => window.panel.t("tel.placeholder")
		}
	}
};

/**
 * @example <k-input :value="tel" @input="tel = $event" name="tel" type="tel" />
 */
export default {
	mixins: [StringInput, props]
};
</script>
