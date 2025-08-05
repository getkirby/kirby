<template>
	<div
		:class="['k-range-input', $attrs.class]"
		:data-disabled="disabled"
		:style="$attrs.style"
	>
		<input
			ref="range"
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
			type="range"
			@input="$emit('input', $event.target.valueAsNumber)"
		/>
		<output v-if="tooltip" :for="id" class="k-range-input-tooltip">
			<span v-if="tooltip.before" class="k-range-input-tooltip-before">{{
				tooltip.before
			}}</span>
			<span
				:style="`--digits: ${maxLength}ch`"
				class="k-range-input-tooltip-text"
			>
				{{ label }}
			</span>
			<span v-if="tooltip.after" class="k-range-input-tooltip-after">{{
				tooltip.after
			}}</span>
		</output>
	</div>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
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
	mixins: [Input, props],
	computed: {
		baseline() {
			// If the minimum is below 0, the baseline should be placed at .
			// Otherwise place the baseline at the minimum
			return this.min < 0 ? 0 : this.min;
		},
		isEmpty() {
			return (
				this.value === "" || this.value === undefined || this.value === null
			);
		},
		label() {
			return this.required || this.value || this.value === 0
				? this.format(this.position)
				: "–";
		},
		maxLength() {
			return Math.floor(Math.abs(this.max)).toString().length;
		},
		position() {
			return this.value || this.value === 0
				? this.value
				: (this.default ?? this.baseline);
		}
	},
	watch: {
		value: {
			handler() {
				this.validate();
			},
			immediate: true
		}
	},
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			this.$el.querySelector("input")?.focus();
		},
		format(value) {
			const locale = document.lang ? document.lang.replace("_", "-") : "en";
			const parts = this.step.toString().split(".");
			const digits = parts.length > 1 ? parts[1].length : 0;
			return new Intl.NumberFormat(locale, {
				minimumFractionDigits: digits
			}).format(value);
		},
		onInput(value) {
			this.$emit("input", value);
		},
		validate() {
			const errors = [];

			if (this.required && this.isEmpty === true) {
				errors.push(this.$t("error.validation.required"));
			}

			if (this.isEmpty === false && this.min && this.value < this.min) {
				errors.push(this.$t("error.validation.min", { min: this.min }));
			}

			if (this.isEmpty === false && this.max && this.value > this.max) {
				errors.push(this.$t("error.validation.max", { max: this.max }));
			}

			this.$refs.range?.setCustomValidity(errors.join(", "));
		}
	}
};
</script>

<style>
.k-range-input {
	--range-track-height: 1px;
	--range-tooltip-back: var(--color-black);

	display: flex;
	align-items: center;
	border-radius: var(--range-track-height);
}
.k-range-input input[type="range"]:focus {
	outline: 0;
}
.k-range-input-tooltip {
	position: relative;
	display: flex;
	align-items: center;
	color: var(--color-white);
	font-size: var(--text-xs);
	font-variant-numeric: tabular-nums;
	line-height: 1;
	text-align: center;
	border-radius: var(--rounded-sm);
	background: var(--range-tooltip-back);
	margin-inline-start: var(--spacing-3);
	padding: 0 var(--spacing-1);
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
.k-range-input-tooltip-text {
	font-family: var(--font-mono);
	width: calc(var(--digits) + var(--spacing-1) * 2);
	text-align: center;
}

.k-range-input[data-disabled="true"] {
	--range-tooltip-back: light-dark(
		var(--color-gray-600),
		var(--color-gray-850)
	);
}

/* Input context */
.k-input[data-type="range"] .k-range-input {
	padding-inline: var(--input-padding);
}
</style>
