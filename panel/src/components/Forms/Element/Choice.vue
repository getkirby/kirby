<template>
	<input
		:id="id"
		ref="input"
		:autofocus="autofocus"
		:checked="checked"
		:data-variant="variant"
		:disabled="disabled"
		:name="name"
		:required="required"
		:type="type"
		:value="value"
		class="k-choice"
		@change="$emit('input', $event.target.checked)"
	/>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	inheritAttrs: false,
	props: {
		checked: {
			type: Boolean
		},
		type: {
			default: "checkbox",
			type: String
		},
		variant: {
			type: String
		},
		value: {
			type: [Boolean, Number, String]
		}
	}
};

export default {
	mixins: [props],
	watch: {
		value: {
			handler() {
				this.validate();
			},
			immediate: true
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		select() {
			this.focus();
		},
		validate() {
			/**
			 * The invalid event is triggered when the input validation fails. This can be used to react on errors immediately.
			 * @event invalid
			 */
			this.$emit("invalid", this.$v.$invalid, this.$v);
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true
			}
		};
	}
};
</script>

<style>
:root {
	--choice-color-back: var(--color-white);
	--choice-color-border: var(--color-gray-500);
	--choice-color-checked: var(--color-black);
	--choice-color-disabled: var(--color-gray-400);
	--choice-color-icon: var(--color-light);
	--choice-color-toggle: var(--choice-color-border);
	--choice-height: 1rem;
	--choice-rounded: var(--rounded-sm);
}

/** Default state **/
input:where([type="checkbox"], [type="radio"]) {
	position: relative;
	cursor: pointer;
	overflow: hidden;
	flex-shrink: 0;
	height: var(--choice-height);
	aspect-ratio: 1/1;
	border: 1px solid var(--choice-color-border);
	appearance: none;
	border-radius: var(--choice-rounded);
	background: var(--choice-color-back);
	box-shadow: var(--shadow-sm);
}

/** Filler **/
input:where([type="checkbox"], input[type="radio"])::after {
	position: absolute;
	content: "";
	inset: 2px;
	border-radius: var(--choice-rounded);
	display: none;
	place-items: center;
	text-align: center;
}

/** Focus state **/
input:where([type="checkbox"], [type="radio"]):focus {
	outline: var(--outline);
	outline-offset: -1px;
	color: var(--color-focus);
}

/** Checked state **/
input:where([type="checkbox"], [type="radio"]):checked::after {
	background: var(--choice-color-checked);
	display: grid;
}

/** Checked focus state **/
input:where([type="checkbox"], [type="radio"]):checked:focus {
	--choice-color-checked: var(--color-focus);
}

/** Disabled state **/
input:where([type="checkbox"], [type="radio"])[disabled] {
	--choice-color-checked: var(--choice-color-disabled);
	--choice-color-border: var(--color-gray-300);
	box-shadow: none;
	cursor: default;
	background: none;
	box-shadow: none;
}

/** Checkbox & Toggle **/
input[type="checkbox"]:checked::after {
	font-size: 8px;
	font-weight: 700;
	content: "✓";
	color: var(--choice-color-icon);
	line-height: 1;
}

/** Radio **/
input[type="radio"] {
	--choice-rounded: 50%;
}

/** Toggle **/
input[type="checkbox"][data-variant="toggle"] {
	--choice-rounded: var(--choice-height);
	aspect-ratio: 2/1;
}
input[type="checkbox"][data-variant="toggle"]::after {
	background: var(--choice-color-toggle);
	display: grid;
	width: 50%;
}
input[type="checkbox"][data-variant="toggle"]:checked::after {
	background: var(--choice-color-checked);
	margin-inline-start: auto;
}
</style>
