<template>
	<k-inputbox
		v-if="mode === 'options'"
		v-bind="$props"
		:icon="null"
		variant="plain"
		type="color"
	>
		<k-coloroption-input v-bind="$props" @input="$emit('input', $event)" />
	</k-inputbox>
	<k-inputbox v-else v-bind="$props" type="color">
		<k-inputbox-element slot="element">
			<template v-if="showPicker === false">
				<span class="k-color-inputbox-preview">
					<k-color-frame :color="value" />
				</span>
			</template>
			<template v-else>
				<button
					:disabled="disabled"
					class="k-color-inputbox-preview"
					type="button"
					@click="$refs.picker.toggle()"
				>
					<k-color-frame :color="value" />
				</button>
				<k-dropdown-content ref="picker">
					<k-colorpicker-input
						ref="color"
						:alpha="alpha"
						:format="format"
						:options="convertedOptions"
						:value="value"
						@input="$emit('input', $event)"
					/>
				</k-dropdown-content>
			</template>
			<k-colorname-input v-bind="$props" @input="$emit('input', $event)" />
		</k-inputbox-element>

		<k-inputbox-description
			v-if="currentOption?.text"
			position="after"
			slot="after"
		>
			{{ currentOption.text }}
		</k-inputbox-description>

		<k-inputbox-icon v-if="showPicker" slot="icon">
			<k-button
				:disabled="disabled"
				:icon="icon"
				:title="$t('open')"
				class="k-inputbox-icon-button"
				@click="$refs.picker.toggle()"
			/>
		</k-inputbox-icon>
	</k-inputbox>
</template>

<script>
import { props as InputboxProps } from "../Inputbox.vue";
import { props as InputProps } from "@/components/Forms/Input/ColornameInput.vue";

export const props = {
	mixins: [InputboxProps, InputProps],
	props: {
		icon: {
			default: "pipette",
			type: String
		},
		/**
		 * @values `picker`, `input`, `options`
		 */
		mode: {
			type: String,
			default: "picker",
			validator: (mode) => ["picker", "input", "options"].includes(mode)
		},
		options: {
			default: () => [],
			type: Array
		},
		picker: {
			default: true,
			type: Boolean
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
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
		},
		showPicker() {
			if (this.mode === "input" || this.picker === false) {
				return false;
			}

			return true;
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
.k-color-inputbox .k-inputbox-before {
	padding-inline-end: 0.25rem;
}
.k-color-inputbox .k-inputbox-element {
	--color-frame-rounded: var(--rounded-sm);
	padding-inline-start: 0.25rem;
}
.k-color-inputbox .k-color-inputbox-preview {
	--color-frame-size: var(--height-sm);
	border-radius: var(--color-frame-rounded);
	height: var(--color-frame-size);
	align-self: center;
}
.k-color-inputbox .k-colorname-input {
	padding: var(--input-padding);
}
.k-color-inputbox .k-colorpicker-input {
	--color-frame-darkness: 100%;
	padding: var(--spacing-1);
}

.k-color-inputbox .k-coloroption-input {
	flex-grow: 1;
}
</style>
