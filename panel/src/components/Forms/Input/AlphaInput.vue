<template>
	<input
		ref="range"
		v-bind="$props"
		:min="0"
		:max="1"
		:step="0.01"
		class="k-alpha-input"
		type="range"
		@input="$emit('input', Number($event.target.value))"
	/>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		value: Number
	}
};

/**
 * @example <k-alpha-input :value="alpha" @input="alpha = $event" />
 */
export default {
	mixins: [props],
	inheritAttrs: false
};
</script>

<style>
.k-alpha-input {
	--range-track-back: linear-gradient(to right, transparent, currentColor);
	color: black;
	background: var(--color-white) var(--pattern-light);
}
</style>
