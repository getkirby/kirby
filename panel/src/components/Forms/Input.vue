<template>
	<div
		:data-disabled="disabled"
		:data-invalid="!novalidate && isInvalid"
		:data-theme="theme"
		:data-type="type"
		class="k-input"
	>
		<span v-if="$slots.before || before" class="k-input-before" @click="focus">
			<slot name="before">{{ before }}</slot>
		</span>
		<span class="k-input-element" @click.stop="focus">
			<slot>
				<component
					:is="'k-' + type + '-input'"
					ref="input"
					v-bind="inputProps"
					:value="value"
					v-on="listeners"
				/>
			</slot>
		</span>
		<span v-if="$slots.after || after" class="k-input-after" @click="focus">
			<slot name="after">{{ after }}</slot>
		</span>
		<span v-if="$slots.icon || icon" class="k-input-icon" @click="focus">
			<slot name="icon">
				<k-icon :type="icon" />
			</slot>
		</span>
	</div>
</template>

<script>
import { after, before, disabled, invalid } from "@/mixins/props.js";

export const props = {
	mixins: [after, before, disabled, invalid],
	props: {
		autofocus: Boolean,
		type: String,
		icon: [String, Boolean],
		theme: String,
		novalidate: {
			type: Boolean,
			default: false
		},
		value: {
			type: [String, Boolean, Number, Object, Array],
			default: null
		}
	}
};

export default {
	mixins: [props],
	data() {
		return {
			isInvalid: this.invalid,
			listeners: {
				...this.$listeners,
				invalid: ($invalid, $v) => {
					this.isInvalid = $invalid;
					this.$emit("invalid", $invalid, $v);
				}
			}
		};
	},
	computed: {
		inputProps() {
			return {
				...this.$props,
				...this.$attrs
			};
		}
	},
	watch: {
		invalid() {
			this.isInvalid = this.invalid;
		}
	},
	methods: {
		blur(e) {
			if (e?.relatedTarget && this.$el.contains(e.relatedTarget) === false) {
				this.trigger(null, "blur");
			}
		},
		focus(e) {
			this.trigger(e, "focus");
		},
		select(e) {
			this.trigger(e, "select");
		},
		trigger(e, method) {
			// prevent focussing on first input element,
			// if click is already targetting another input element
			if (
				e?.target?.tagName === "INPUT" &&
				typeof e?.target?.[method] === "function"
			) {
				e.target[method]();
				return;
			}

			// use dedicated focus method if provided
			if (typeof this.$refs.input?.[method] === "function") {
				this.$refs.input[method]();
				return;
			}

			const input = this.$el.querySelector("input, select, textarea");

			if (typeof input?.[method] === "function") {
				input[method]();
			}
		}
	}
};
</script>

<style>
/* Base Design */
.k-input {
	display: flex;
	align-items: center;
	line-height: 1;
	border: 0;
	outline: 0;
	background: none;
}
.k-input-element {
	flex-grow: 1;
}
.k-input-icon {
	display: flex;
	justify-content: center;
	align-items: center;
	line-height: 0;
}

/* Disabled state */
.k-input[data-disabled="true"] {
	pointer-events: none;
}

[data-disabled="true"] .k-input-icon {
	color: var(--color-gray-600);
}

.k-input[data-theme="field"] {
	line-height: 1;
	outline: var(--field-input-border);
	background: var(--field-input-background);
	border-radius: var(--rounded);
}
.k-input[data-theme="field"]:focus-within {
	outline: var(--outline);
}

.k-input[data-theme="field"][data-disabled="true"] {
	background: var(--color-background);
}

.k-input[data-theme="field"] .k-input-icon {
	width: var(--field-input-height);
}
.k-input[data-theme="field"] .k-input-icon,
.k-input[data-theme="field"] .k-input-before,
.k-input[data-theme="field"] .k-input-after {
	align-self: stretch;
	display: flex;
	align-items: center;
	flex-shrink: 0;
}
.k-input[data-theme="field"] .k-input-before,
.k-input[data-theme="field"] .k-input-after {
	padding: 0 var(--field-input-padding);
}
.k-input[data-theme="field"] .k-input-before {
	color: var(--field-input-color-before);
	padding-inline-end: 0;
}
.k-input[data-theme="field"] .k-input-after {
	color: var(--field-input-color-after);
	padding-inline-start: 0;
}

.k-input[data-theme="field"] .k-input-icon > .k-dropdown {
	width: 100%;
	height: 100%;
}
.k-input[data-theme="field"] .k-input-icon-button {
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

.k-input[data-theme="field"] .k-number-input,
.k-input[data-theme="field"] .k-select-input,
.k-input[data-theme="field"] .k-text-input {
	padding: var(--field-input-padding);
	line-height: var(--field-input-line-height);
	border-radius: var(--rounded);
}

.k-input[data-theme="field"] .k-date-input .k-select-input,
.k-input[data-theme="field"] .k-time-input .k-select-input {
	padding-inline: 0;
}

.k-input[data-theme="field"] .k-date-input .k-select-input:first-child,
.k-input[data-theme="field"] .k-time-input .k-select-input:first-child {
	padding-inline-start: var(--field-input-padding);
}

.k-input[data-theme="field"] .k-date-input .k-select-input:focus-within,
.k-input[data-theme="field"] .k-time-input .k-select-input:focus-within {
	color: var(--color-focus);
	font-weight: var(--font-bold);
}
.k-input[data-theme="field"].k-time-input .k-time-input-meridiem {
	padding-inline-start: var(--field-input-padding);
}

/* Range */
.k-input[data-theme="field"][data-type="range"] .k-range-input {
	padding: var(--field-input-padding);
}

/* Select Boxes */
.k-input[data-theme="field"][data-type="select"] {
	position: relative;
}
.k-input[data-theme="field"][data-type="select"] .k-input-icon {
	position: absolute;
	inset-block: 0;
	inset-inline-end: 0;
}

</style>
