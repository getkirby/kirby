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
		:class="`k-${type}-input`"
		class="k-string-input"
		@input="$emit('input', $event.target.value)"
	/>
</template>

<script>
import {
	autocomplete,
	autofocus,
	disabled,
	id,
	name,
	pattern,
	placeholder,
	required,
	spellcheck
} from "@/mixins/props.js";

export const props = {
	mixins: [
		autocomplete,
		autofocus,
		disabled,
		id,
		name,
		pattern,
		placeholder,
		required,
		spellcheck
	],
	props: {
		font: String,
		maxlength: Number,
		minlength: Number,
		type: String,
		value: String
	}
};

/**
 * @example <k-string-input :value="value" type="text" @input="value = $event" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"]
};
</script>

<style>
/* Inputbox context */
.k-inputbox .k-string-input {
	line-height: 1.25;
	padding-inline: var(--input-padding);
}
.k-inputbox .k-string-input:focus {
	outline: none;
}
</style>
