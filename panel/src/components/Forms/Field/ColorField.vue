<template>
	<k-field v-bind="$props" :input="_uid" class="k-color-field">
		<!-- Mode: options -->
		<k-coloroptions-input
			v-if="mode === 'options'"
			:name="name"
			:options="convertedOptions"
			:value="value"
			class="k-color-field-options"
			@input="$emit('input', $event)"
		/>

		<!-- Mode: picker/input -->
		<k-input
			v-else
			v-bind="$props"
			:id="_uid"
			ref="input"
			theme="field"
			type="color"
			@input="$emit('input', $event)"
			@invalid="isInvalid = $event ?? false"
			@submit="$emit('submit')"
		>
			<template #before>
				<template v-if="mode === 'picker'">
					<button
						:disabled="disabled"
						class="k-color-field-picker-toggle"
						type="button"
						@click="$refs.picker.toggle()"
					>
						<k-color-frame :color="!isInvalid ? value : null" />
					</button>
					<k-dropdown-content ref="picker" class="k-color-field-picker">
						<k-colorpicker-input
							ref="color"
							:alpha="alpha"
							:options="convertedOptions"
							:required="required"
							:value="value"
							@input="onPicker"
						/>
					</k-dropdown-content>
				</template>
				<k-color-frame v-else :color="!isInvalid ? value : null" />
			</template>

			<template v-if="currentOption?.text" #after>
				{{ currentOption.text }}
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
import { props as ColorInput } from "../Input/ColorInput.vue";

export default {
	mixins: [Field, Input, ColorInput],
	inheritAttrs: false,
	props: {
		icon: {
			type: String,
			default: "pipette"
		},
		/**
		 * @values `picker`, `input`, `options`
		 */
		mode: {
			type: String,
			default: "picker",
			validator: (mode) => ["picker", "input", "options"].includes(mode)
		},
		/**
		 * Array of color options { value, key }
		 */
		options: {
			type: Array,
			default: () => []
		}
	},
	data() {
		return {
			isInvalid: false
		};
	},
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
		},
		onPicker(hsv) {
			if (!hsv) {
				return this.$emit("input", "");
			}

			const input = this.convert(hsv);
			this.$emit("input", input);
		}
	}
};
</script>

<style>
.k-color-field {
	--color-frame-size: calc(var(--input-height) - var(--spacing-2));
}
.k-color-field .k-input .k-input-before {
	align-items: center;
	padding-inline: var(--spacing-1);
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
</style>
