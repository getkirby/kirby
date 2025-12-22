<template>
	<div
		:data-disabled="disabled"
		:data-translate="translate"
		:class="[
			'k-field',
			`k-field-name-${name}`,
			`k-field-type-${type}`,
			$attrs.class
		]"
		:style="$attrs.style"
		@focusin="$emit('focus', $event)"
		@focusout="$emit('blur', $event)"
	>
		<slot name="header">
			<header
				v-if="label || $slots.label || $slots.options || $slots.counter"
				class="k-field-header"
			>
				<slot name="label">
					<k-label
						v-if="label"
						:has-diff="hasDiff"
						:input="input"
						:required="required"
						:title="label"
						type="field"
					>
						{{ label }}
					</k-label>
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
import { disabled, help, id, label, name, required } from "@/mixins/props.js";

export const props = {
	mixins: [disabled, help, id, label, name, required],
	props: {
		counter: [Boolean, Object],
		endpoints: Object,
		hasDiff: Boolean,
		input: {
			type: [String, Number, Boolean],
			default: null
		},
		translate: Boolean,
		type: String
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	emits: ["blur", "focus"]
};
</script>

<style>
.k-field[data-disabled="true"] {
	cursor: not-allowed;
}
.k-field-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	position: relative;
	margin-bottom: var(--spacing-2);
}
.k-field-options {
	flex-shrink: 0;
}
.k-field-buttons {
	flex-shrink: 0;
}
.k-field-counter {
	display: none;
}
.k-field:focus-within > .k-field-header > .k-field-counter {
	display: block;
}
.k-field-footer {
	margin-top: var(--spacing-2);
}
</style>
