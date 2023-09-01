<template>
	<label :data-disabled="disabled" class="k-range-input">
		<k-range
			ref="input"
			v-bind="{
				autofocus,
				disabled,
				id,
				max,
				min,
				name,
				required,
				step
			}"
			:value="position"
			@input="$emit('input', $event)"
		/>
		<span v-if="tooltip" class="k-range-input-tooltip">
			<span v-if="tooltip.before" class="k-range-input-tooltip-before">{{
				tooltip.before
			}}</span>
			<span class="k-range-input-tooltip-text">{{ label }}</span>
			<span v-if="tooltip.after" class="k-range-input-tooltip-after">{{
				tooltip.after
			}}</span>
		</span>
	</label>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minValue as validateMinValue,
	maxValue as validateMaxValue
} from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		default: [Number, String],
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
			type: [Number, String],
			default: 1
		},
		/**
		 * The slider tooltip can have text before and after the value.
		 */
		tooltip: {
			type: [Boolean, Object],
			default() {
				return {
					before: null,
					after: null
				};
			}
		},
		value: [Number, String]
	}
};

/**
 * @example <k-input :value="range" @input="range = $event" name="range" type="range" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		baseline() {
			// If the minimum is below 0, the baseline should be placed at .
			// Otherwise place the baseline at the minimum
			return this.min < 0 ? 0 : this.min;
		},
		label() {
			return this.required || this.value || this.value === 0
				? this.format(this.position)
				: "–";
		},
		position() {
			return this.value || this.value === 0
				? this.value
				: this.default ?? this.baseline;
		}
	},
	watch: {
		position() {
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		format(value) {
			const locale = document.lang ? document.lang.replace("_", "-") : "en";
			const parts = this.step.toString().split(".");
			const digits = parts.length > 1 ? parts[1].length : 0;
			return new Intl.NumberFormat(locale, {
				minimumFractionDigits: digits
			}).format(value);
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		onInput(value) {
			this.$emit("input", value);
		}
	},
	validations() {
		return {
			position: {
				required: this.required ? validateRequired : true,
				min: this.min ? validateMinValue(this.min) : true,
				max: this.max ? validateMaxValue(this.max) : true
			}
		};
	}
};
</script>

<style>
.k-range-input {
	--range-track-height: 1px;
	--range-track-back: var(--color-gray-300);
	--range-tooltip-back: var(--color-black);
	display: flex;
	align-items: center;
	padding: var(--field-input-padding);
}
.k-range-input input[type="range"]:focus {
	outline: 0;
}
.k-range-input-tooltip {
	position: relative;
	max-width: 20%;
	display: flex;
	align-items: center;
	color: var(--color-white);
	font-size: var(--text-xs);
	font-variant-numeric: tabular-nums;
	line-height: 1;
	text-align: center;
	border-radius: var(--rounded-sm);
	background: var(--range-tooltip-back);
	margin-inline-start: 1rem;
	padding: 0 0.25rem;
	white-space: nowrap;
}
.k-range-input-tooltip::after {
	position: absolute;
	top: 50%;
	inset-inline-start: -3px;
	width: 0;
	height: 0;
	transform: translateY(-50%);
	border-block: 3px solid transparent;
	border-inline-end: 3px solid var(--range-tooltip-back);
	content: "";
}
.k-range-input-tooltip > * {
	padding: var(--spacing-1);
}

.k-range-input[data-disabled="true"] {
	--range-tooltip-back: var(--color-gray-600);
}
</style>
