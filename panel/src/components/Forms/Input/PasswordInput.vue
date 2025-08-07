<template>
	<k-string-input
		v-bind="$props"
		type="password"
		class="k-password-input"
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
 * @example <k-input :value="password" @input="password = $event" name="password" type="password" />
 */
export default {
	mixins: [StringInput, props],
	emits: ["input"]
};
</script>
