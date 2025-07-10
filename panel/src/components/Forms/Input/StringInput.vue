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
		:class="['k-string-input', $attrs.class]"
		:data-font="font"
		:style="$attrs.style"
		@input="$emit('input', $event.target.value)"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import {
	autocomplete,
	autofocus,
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
		autofocus,
		font,
		maxlength,
		minlength,
		pattern,
		placeholder,
		spellcheck
	],
	props: {
		ariaLabel: String,
		preselect: Boolean,
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
	mixins: [Input, props],
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		select() {
			this.$el.select();
		}
	}
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
.k-string-input:disabled::placeholder {
	opacity: 0;
}
</style>
