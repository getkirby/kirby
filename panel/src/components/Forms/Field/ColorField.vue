<template>
	<k-field
		v-bind="$props"
		:class="['k-color-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<!-- Mode: options -->
		<k-coloroptions-input
			v-if="mode === 'options'"
			v-bind="$props"
			:options="convertedOptions"
			class="k-color-field-options"
			@input="$emit('input', $event)"
		/>

		<!-- Mode: picker/input -->
		<k-input v-else v-bind="$props" type="color">
			<template #before>
				<template v-if="mode === 'picker'">
					<button
						:disabled="disabled"
						class="k-color-field-picker-toggle"
						type="button"
						@click="$refs.picker.toggle()"
					>
						<k-color-frame :color="value" />
					</button>
					<k-dropdown ref="picker" class="k-color-field-picker">
						<k-colorpicker-input
							ref="color"
							v-bind="$props"
							:options="convertedOptions"
							@input="$emit('input', $event)"
							@click.stop
						/>
					</k-dropdown>
				</template>
				<k-color-frame v-else :color="value" />
			</template>

			<template #default>
				<k-colorname-input v-bind="$props" @input="$emit('input', $event)" />
			</template>

			<template v-if="currentOption?.text" #after>
				<!-- eslint-disable-next-line vue/no-v-html -->
				<span v-html="currentOption.text" />
			</template>

			<template v-if="mode === 'picker'" #icon>
				<k-button
					:icon="icon"
					class="k-input-icon-button"
					@click.stop="$refs.picker.toggle()"
				/>
			</template>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as ColornameInput } from "../Input/ColornameInput.vue";

/**
 * @since 4.0.0
 */
export default {
	mixins: [Field, Input, ColornameInput],
	inheritAttrs: false,
	props: {
		icon: {
			type: String,
			default: "pipette"
		},
		/**
		 * Display mode
		 * @values "picker", "input", "options"
		 */
		mode: {
			type: String,
			default: "picker",
			validator: (mode) => ["picker", "input", "options"].includes(mode)
		},
		/**
		 * Array of color options
		 */
		options: {
			type: Array,
			default: () => []
		}
	},
	emits: ["input"],
	computed: {
		convertedOptions() {
			return this.options.map((option) => ({
				...option,
				value: this.convert(option.value)
			}));
		},
		currentOption() {
			return this.convertedOptions.find((color) => color.value === this.value);
		}
	},
	methods: {
		convert(value) {
			return this.$library.colors.toString(value, this.format, this.alpha);
		}
	}
};
</script>

<style>
.k-color-field {
	--color-frame-size: calc(var(--input-height) - var(--spacing-2));
}
.k-color-field .k-input-before {
	align-items: center;
	padding-inline-start: var(--spacing-1);
}

/* Mode: options */
.k-color-field-options {
	--color-frame-size: var(--input-height);
}

/* Mode: picker */
.k-color-field-picker {
	padding: var(--spacing-3);
}
.k-color-field-picker-toggle {
	--color-frame-rounded: var(--rounded-sm);
	border-radius: var(--color-frame-rounded);
}

.k-color-field .k-colorname-input {
	padding-inline: var(--input-padding);
}
.k-color-field .k-colorname-input:focus {
	outline: 0;
}
</style>
