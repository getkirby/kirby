<template>
	<label
		:aria-disabled="disabled"
		:class="['k-choice-input', $attrs.class]"
		:style="$attrs.style"
	>
		<input
			v-bind="{
				autofocus,
				id,
				checked,
				disabled,
				name,
				required,
				type,
				value
			}"
			:class="[variant === 'invisible' ? 'sr-only' : null, $attrs.class]"
			:data-variant="variant"
			@input="$emit('input', $event.target.checked)"
		/>
		<span v-if="label || info" class="k-choice-input-label">
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span class="k-choice-input-label-text" v-html="label" />
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span v-if="info" class="k-choice-input-label-info" v-html="info" />
		</span>
	</label>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		checked: {
			type: Boolean
		},
		info: {
			type: String
		},
		label: {
			type: String
		},
		type: {
			default: "checkbox",
			type: String
		},
		value: {
			type: [Boolean, Number, String]
		},
		variant: {
			type: String
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-choice-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [Input, props],
	emits: ["input"]
};
</script>

<style>
.k-choice-input {
	display: flex;
	gap: var(--spacing-3);
	min-width: 0;
}
.k-choice-input input {
	top: 2px;
}
.k-choice-input-label {
	display: flex;
	line-height: 1.25rem;
	flex-direction: column;
	min-width: 0;
	color: var(--choice-color-text);
}
.k-choice-input-label > * {
	display: block;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-choice-input-label-info {
	color: var(--choice-color-info);
}
.k-choice-input[aria-disabled="true"] {
	cursor: not-allowed;
}

/* Field context */
:where(.k-checkboxes-field, .k-radio-field) .k-choice-input {
	min-height: var(--input-height);
	padding-block: var(--spacing-2);
	padding-inline: var(--spacing-3);
	border-radius: var(--input-rounded);
}

:where(.k-checkboxes-field, .k-radio-field) .k-choice-input {
	background: var(--item-color-back);
	box-shadow: var(--shadow);
}
</style>
