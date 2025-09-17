<template>
	<div
		:class="['k-input', $attrs.class]"
		:data-disabled="disabled"
		:data-type="type"
		:style="$attrs.style"
	>
		<span
			v-if="$slots.before || before"
			class="k-input-description k-input-before"
			@click="focus"
		>
			<slot name="before">{{ before }}</slot>
		</span>
		<span class="k-input-element" @click.stop="focus">
			<slot>
				<component
					:is="'k-' + type + '-input'"
					ref="input"
					v-bind="inputProps"
					:value="value"
					@input="$emit('input', $event)"
					@submit="$emit('submit', $event)"
				/>
			</slot>
		</span>
		<span
			v-if="$slots.after || after"
			class="k-input-description k-input-after"
			@click="focus"
		>
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
import { after, before, disabled } from "@/mixins/props.js";

export const props = {
	mixins: [after, before, disabled],
	inheritAttrs: false,
	props: {
		autofocus: Boolean,
		type: String,
		icon: [String, Boolean],
		value: {
			type: [String, Boolean, Number, Object, Array],
			default: null
		}
	}
};

export default {
	mixins: [props],
	emits: ["input", "submit"],
	computed: {
		inputProps() {
			return {
				...this.$props,
				...this.$attrs
			};
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
:root {
	--input-color-back: light-dark(var(--color-white), var(--color-gray-850));
	--input-color-border: var(--color-border);
	--input-color-description: var(--color-text-dimmed);
	--input-color-icon: currentColor;
	--input-color-placeholder: var(--color-gray-600);
	--input-color-text: currentColor;
	--input-font-family: var(--font-sans);
	--input-font-size: var(--text-sm);
	--input-height: 2.25rem;
	--input-leading: 1;
	--input-outline-focus: var(--outline);
	--input-padding: var(--spacing-2);
	--input-padding-multiline: 0.475rem var(--input-padding);
	--input-rounded: var(--rounded);
	--input-shadow: none;
}

@media (pointer: coarse) {
	:root {
		--input-font-size: var(--text-md);
		--input-padding-multiline: 0.375rem var(--input-padding);
	}
}

/* Base Design */
.k-input {
	display: flex;
	align-items: center;
	line-height: var(--input-leading);
	border: 0;
	background: var(--input-color-back);
	border-radius: var(--input-rounded);
	outline: 1px solid var(--input-color-border);
	color: var(--input-color-text);
	min-height: var(--input-height);
	box-shadow: var(--input-shadow);
	font-family: var(--input-font-family);
	font-size: var(--input-font-size);
}
.k-input:not([data-disabled="true"]):focus-within {
	outline: var(--input-outline-focus);
}

/* Element container */
.k-input-element {
	flex-grow: 1;
	min-width: 0;
}

/* Icon */
.k-input-icon {
	color: var(--input-color-icon);
	display: flex;
	justify-content: center;
	align-items: center;
	width: var(--input-height);
}
.k-input-icon-button {
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}

/* Before and After Text */
.k-input-description {
	color: var(--input-color-description);
	padding-inline: var(--input-padding);
}
.k-input-before {
	padding-inline-end: 0;
}
.k-input-after {
	padding-inline-start: 0;
}

/* Icon and description alignment */
.k-input :where(.k-input-description, .k-input-icon) {
	align-self: stretch;
	display: flex;
	align-items: center;
	flex-shrink: 0;
}

/* Disabled state */
.k-input[data-disabled="true"] {
	--input-color-back: var(--panel-color-back);
	--input-color-icon: var(--color-gray-600);
}
</style>
