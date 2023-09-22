<template>
	<input
		v-bind="{
			autofocus,
			disabled,
			id,
			max,
			min,
			name,
			required,
			step,
			value
		}"
		class="k-range-input"
		type="range"
		@input="$emit('input', $event.target.valueAsNumber)"
	/>
</template>

<script>
import { props as InputProps } from "@/mixins/input.js";
import Input from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		/**
		 * The highest accepted number
		 */
		max: {
			type: Number,
			default: 100
		},
		/**
		 * The lowest required number
		 */
		min: {
			type: Number,
			default: 0
		},
		/**
		 * The amount to increment when dragging the slider. This can be a decimal.
		 */
		step: {
			type: Number
		},
		value: Number
	}
};

/**
 * @example <k-range-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
	computed: {
		isEmpty() {
			return (
				this.value === "" || this.value === undefined || this.value === null
			);
		}
	},
	watch: {
		value() {
			this.validate();
		}
	},
	mounted() {
		this.validate();
	},
	methods: {
		validate() {
			let error = "";

			if (this.required && this.isEmpty === true) {
				error = this.$t("error.validation.required");
			} else if (this.isEmpty === false && this.min && this.value < this.min) {
				error = this.$t("error.validation.min", { min: this.min });
			} else if (this.isEmpty === false && this.max && this.value > this.max) {
				error = this.$t("error.validation.max", { max: this.max });
			}

			this.$el.setCustomValidity(error);
		}
	}
};
</script>
