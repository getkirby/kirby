<template>
	<input
		ref="input"
		v-bind="{
			autofocus,
			disabled,
			id,
			max,
			min,
			name,
			placeholder,
			required
		}"
		:value="number"
		:step="step"
		class="k-number-input"
		type="number"
		@keydown.ctrl.s="clean"
		@keydown.meta.s="clean"
		v-on="listeners"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { placeholder } from "@/mixins/props.js";

import {
	required as validateRequired,
	minValue as validateMinValue,
	maxValue as validateMaxValue
} from "vuelidate/lib/validators";

export const props = {
	mixins: [InputProps, placeholder],
	props: {
		max: Number,
		min: Number,
		name: [Number, String],
		preselect: Boolean,
		/**
		 * The amount to increment with each input step. This can be a decimal.
		 * Use "any" to allow any decimal value.
		 */
		step: [Number, String],
		value: {
			type: [Number, String],
			default: ""
		}
	}
};

/**
 * @example <k-input :value="number" @input="number = $event" name="number" type="number" />
 */
export default {
	mixins: [Input, props],
	data() {
		return {
			number: this.format(this.value),
			timeout: null,
			listeners: {
				...this.$listeners,
				input: (event) => this.onInput(event.target.value),
				blur: this.onBlur
			}
		};
	},
	watch: {
		value(value) {
			this.number = value;
		},
		number: {
			immediate: true,
			handler() {
				this.onInvalid();
			}
		}
	},
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		decimals() {
			const step = Number(this.step ?? 0);

			if (Math.floor(step) === step) {
				return 0;
			}

			if (step.toString().indexOf("e") !== -1) {
				return parseInt(
					step.toFixed(16).split(".")[1].split("").reverse().join("")
				).toString().length;
			}

			return step.toString().split(".")[1].length ?? 0;
		},
		format(value) {
			if (isNaN(value) || value === "") {
				return "";
			}

			// Handle "any" step value
			if (this.step === "any") {
				return value;
			}

			const decimals = this.decimals();

			if (decimals) {
				return parseFloat(value).toFixed(decimals);
			}

			if (Number.isInteger(this.step)) {
				return parseInt(value);
			}

			return parseFloat(value);
		},
		clean() {
			this.number = this.format(this.number);
		},
		emit(value) {
			value = parseFloat(value);

			if (isNaN(value)) {
				value = "";
			}

			if (value !== this.value) {
				this.$emit("input", value);
			}
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		onInput(value) {
			this.number = value;
			this.emit(value);
		},
		onBlur() {
			this.clean();
			this.emit(this.number);
		},
		select() {
			this.$refs.input.select();
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true,
				min: this.min ? validateMinValue(this.min) : true,
				max: this.max ? validateMaxValue(this.max) : true
			}
		};
	}
};
</script>

<style>
.k-number-input {
	padding: var(--input-padding);
	border-radius: var(--input-rounded);
}
.k-number-input:focus {
	outline: 0;
}
</style>
