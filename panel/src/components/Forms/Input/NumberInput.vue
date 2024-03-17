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
		:class="['k-number-input', $attrs.class]"
		:step="stepNumber"
		:style="$attrs.style"
		:value="number"
		type="number"
		@blur="onBlur"
		@input="onInput($event.target.value)"
		@keydown.ctrl.s="clean"
		@keydown.meta.s="clean"
	/>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { placeholder } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, placeholder],
	props: {
		max: Number,
		min: Number,
		name: [Number, String],
		preselect: Boolean,
		/**
		 * The amount to increment with each input step. This can be a decimal.
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
			stepNumber: this.format(this.step),
			timeout: null
		};
	},
	watch: {
		value(value) {
			this.number = value;
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

			const decimals = this.decimals();

			if (decimals) {
				value = parseFloat(value).toFixed(decimals);
			} else if (Number.isInteger(this.step)) {
				value = parseInt(value);
			} else {
				value = parseFloat(value);
			}

			return value;
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
