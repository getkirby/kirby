<template>
	<div
		:data-disabled="disabled"
		:data-translate="translate"
		:class="'k-field k-field-name-' + name"
		@focusin="$emit('focus', $event)"
		@focusout="$emit('blur', $event)"
	>
		<slot name="header">
			<header class="k-field-header">
				<slot name="label">
					<label :for="input" class="k-field-label">
						{{ labelText }}
						<abbr v-if="required" :title="$t('field.required')">*</abbr>
					</label>
				</slot>
				<slot name="options" />
				<slot name="counter">
					<k-counter
						v-if="counter"
						v-bind="counter"
						:required="required"
						class="k-field-counter"
					/>
				</slot>
			</header>
		</slot>
		<slot />
		<slot name="footer">
			<footer v-if="help || $slots.help" class="k-field-footer">
				<slot name="help">
					<k-text v-if="help" class="k-help k-field-help" :html="help" />
				</slot>
			</footer>
		</slot>
	</div>
</template>

<script>
import { disabled, help, label, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [disabled, help, label, name, required],
	props: {
		counter: [Boolean, Object],
		endpoints: Object,
		input: [String, Number],
		translate: Boolean,
		type: String
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		labelText() {
			return this.label || " ";
		}
	}
};
</script>

<style>
.k-field-label {
	font-weight: var(--font-bold);
	display: block;
	flex-grow: 1;
	line-height: 1.25rem;
}
.k-field-label abbr {
	text-decoration: none;
	color: var(--color-gray-500);
	padding-inline-start: 0.25rem;
}
.k-field-header {
	position: relative;
	display: flex;
	align-items: center;
	padding-bottom: var(--spacing-2);
}
.k-field[data-disabled="true"] {
	cursor: not-allowed;
}
.k-field[data-disabled="true"] * {
	pointer-events: none;
}
.k-field[data-disabled="true"] .k-text[data-theme="help"] * {
	pointer-events: initial;
}
.k-field-counter {
	display: none;
}
.k-field:focus-within > .k-field-header > .k-field-counter {
	display: block;
}
.k-field > :has(+ footer) {
	margin-bottom: var(--spacing-1);
}
</style>
