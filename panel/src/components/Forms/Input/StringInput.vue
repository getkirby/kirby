<template>
	<input
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
		v-direction
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
 * @example <k-string-input :value="value" type="text" @input="value = $event" />
 * @public
 */
export default {
	mixins: [Input, props]
};
</script>
