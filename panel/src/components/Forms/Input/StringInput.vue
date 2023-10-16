<template>
	<input
		v-direction
		v-bind="{
			autocomplete,
			autofocus,
			disabled,
			id,
			maxlength,
			minlength,
			name,
			pattern,
			placeholder,
			required,
			spellcheck,
			type,
			value
		}"
		:aria-label="ariaLabel"
		:data-font="font"
		class="k-string-input"
		@input="$emit('input', $event.target.value)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import {
	autocomplete,
	font,
	maxlength,
	minlength,
	pattern,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

export const props = {
	mixins: [
		InputProps,
		autocomplete,
		font,
		maxlength,
		minlength,
		pattern,
		placeholder,
		spellcheck
	],
	props: {
		ariaLabel: String,
		type: {
			default: "text",
			type: String
		},
		value: {
			type: String
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-string-input :value="value" type="text" @input="value = $event" />
 */
export default {
	mixins: [Input, props]
};
</script>

<style>
.k-string-input {
	padding: var(--input-padding);
	border-radius: var(--input-rounded);
}
.k-string-input:focus {
	outline: 0;
}
.k-string-input[data-font="monospace"] {
	font-family: var(--font-mono);
}
</style>
