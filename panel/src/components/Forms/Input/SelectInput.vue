<template>
	<select
		v-bind="{
			autofocus,
			disabled,
			id,
			multiple,
			name,
			required
		}"
		:aria-label="ariaLabel"
		:data-empty="isEmpty"
		class="k-select-input"
		@input="$emit('input', $event.target.value)"
	>
		<option v-if="empty !== false" value="">
			{{ placeholder }}
		</option>
		<option
			v-for="option in options"
			:key="option.value"
			:disabled="option.disabled"
			:selected="option.value === value"
			:value="option.value"
		>
			{{ option.text }}
		</option>
	</select>
</template>

<script>
import { props as InputProps } from "@/mixins/input.js";
import Input from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		ariaLabel: String,
		/**
		 * Show/hide the empty option
		 */
		empty: {
			default: true,
			type: Boolean
		},
		multiple: {
			default: false,
			type: Boolean
		},
		options: {
			default: () => [],
			type: Array
		},
		/**
		 * Placeholder text when no option is selected yet.
		 */
		placeholder: {
			default: "–",
			type: String
		},
		value: {
			default: "",
			type: [String, Number, Boolean]
		}
	}
};

/**
 * @example <k-select-input :options="options" :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [Input, props],
	computed: {
		isEmpty() {
			if (this.multiple) {
				return false;
			}

			return (
				this.value === null || this.value === undefined || this.value === ""
			);
		}
	}
};
</script>

<style>
.k-select-input[data-empty] {
	color: var(--input-color-placeholder);
}
</style>
