<template>
	<k-inputbox v-bind="$props" type="range">
		<k-range-input v-bind="$props" @input="$emit('input', $event)" />
		<output v-if="tooltip" class="k-range-inputbox-tooltip">
			<span v-if="tooltip.before" class="k-range-input-tooltip-before">
				{{ tooltip.before }}
			</span>
			<span class="k-range-inputbox-tooltip-text">{{ label }}</span>
			<span v-if="tooltip.after" class="k-range-inputbox-tooltip-after">
				{{ tooltip.after }}
			</span>
		</output>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/RangeInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		/**
		 * The slider tooltip can have text before and after the value.
		 */
		tooltip: {
			default: true,
			type: [Boolean, Object]
		}
	}
};

/**
 * @example <k-range-inputbox :value="value" @input="value = $event" />
 */
export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["input"],
	computed: {
		label() {
			return this.required || this.value || this.value === 0
				? this.format(this.value)
				: "–";
		}
	},
	methods: {
		format(value) {
			const locale = document.lang ? document.lang.replace("_", "-") : "en";
			const parts = this.step?.toString().split(".") ?? [];
			const digits = parts.length > 1 ? parts[1].length : 0;
			return new Intl.NumberFormat(locale, {
				minimumFractionDigits: digits
			}).format(value);
		}
	}
};
</script>

<style>
.k-range-inputbox {
	--range-tooltip-back: var(--color-black);
	--range-track-height: 1px;
}
.k-range-inputbox .k-inputbox-element {
	display: flex;
	align-items: center;
	padding-inline: var(--inputbox-padding);
}
.k-range-inputbox-tooltip {
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
.k-range-inputbox-tooltip::after {
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
.k-range-inputbox-tooltip > * {
	padding: var(--spacing-1);
}

.k-range-inputbox[aria-disabled="true"] {
	--range-tooltip-back: var(--color-gray-600);
}
</style>
