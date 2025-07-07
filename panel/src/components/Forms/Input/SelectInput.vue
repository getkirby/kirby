<template>
	<span
		:class="['k-select-input', $attrs.class]"
		:data-disabled="disabled"
		:data-empty="isEmpty"
		:style="$attrs.style"
	>
		<select
			:id="id"
			ref="input"
			:autofocus="autofocus"
			:aria-label="ariaLabel"
			:disabled="disabled"
			:formnovalidate="novalidate"
			:name="name"
			:required="required"
			:value="value"
			class="k-select-input-native"
			@change="$emit('input', $event.target.value)"
			@click="onClick"
		>
			<option v-if="hasEmptyOption" :disabled="required" value="">
				{{ empty }}
			</option>
			<option
				v-for="option in options"
				:key="option.value"
				:disabled="option.disabled"
				:value="option.value"
			>
				{{ option.text }}
			</option>
		</select>
		{{ label }}
	</span>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { options, placeholder } from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, options, placeholder],
	props: {
		ariaLabel: String,
		value: {
			type: [String, Number, Boolean],
			default: ""
		}
	}
};

export default {
	mixins: [Input, props],
	emits: ["click", "input"],
	computed: {
		empty() {
			return this.placeholder ?? "—";
		},
		hasEmptyOption() {
			// empty option is only displayed if the field is
			// not required or currently has no value yet
			return !this.required || this.isEmpty;
		},
		isEmpty() {
			return (
				this.value === null || this.value === undefined || this.value === ""
			);
		},
		label() {
			const label = this.text(this.value);

			if (this.isEmpty || label === null) {
				return this.empty;
			}

			return label;
		}
	},
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		onClick(event) {
			event.stopPropagation();
			this.$emit("click", event);
		},
		select() {
			this.focus();
		},
		text(value) {
			let text = null;

			for (const option of this.options) {
				if (option.value == value) {
					text = option.text;
				}
			}

			return text;
		}
	}
};
</script>

<style>
.k-select-input {
	position: relative;
	display: block;
	overflow: hidden;
	padding: var(--input-padding);
	border-radius: var(--input-rounded);
}
.k-select-input[data-empty="true"] {
	color: var(--input-color-placeholder);
}

.k-select-input-native {
	position: absolute;
	inset: 0;
	opacity: 0;
	z-index: 1;
}
.k-select-input-native[disabled] {
	cursor: default;
}

/* Input context */
.k-input[data-type="select"] {
	position: relative;
}
.k-input[data-type="select"] .k-input-icon {
	position: absolute;
	inset-block: 0;
	inset-inline-end: 0;
}
</style>
