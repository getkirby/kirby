<template>
	<k-field v-bind="$props" :input="_uid" class="k-color-field">
		<!-- Mode: options -->
		<div
			v-if="mode === 'options'"
			style="--preview-width: var(--field-input-height)"
			class="k-color-field-options"
		>
			<button
				v-for="color in convertedOptions"
				:key="color.value"
				:aria-current="color.value === currentOption?.value"
				:style="'color: ' + color.value"
				:title="color.text ?? color.value"
				class="k-color-preview"
				type="button"
				@click="onOption(color)"
			/>
		</div>

		<!-- Mode: picker/input -->
		<k-input
			v-else
			v-bind="$props"
			:id="_uid"
			ref="input"
			theme="field"
			type="color"
			@input="onInput"
			@invalid="isInvalid = $event ?? false"
			@submit="$emit('submit')"
		>
			<template #before>
				<template v-if="mode === 'picker'">
					<k-dropdown>
						<k-button
							:style="!isInvalid ? 'color: ' + value : null"
							class="k-color-field-preview k-color-preview"
							@click="$refs.picker.toggle()"
						/>
						<k-dropdown-content
							ref="picker"
							class="k-color-field-picker"
							@open="$nextTick(setPicker)"
						>
							<k-color
								ref="color"
								:alpha="alpha"
								@input="onPicker($event.target.value)"
							/>

							<div class="k-color-field-options">
								<button
									v-for="color in convertedOptions"
									:key="color.value"
									:style="'color: ' + color.value"
									:title="color.text ?? color.value"
									type="button"
									class="k-color-preview"
									@click="$refs.input.$refs.input.onPaste(color.value)"
								/>
							</div>
						</k-dropdown-content>
					</k-dropdown>
				</template>
				<div
					v-else
					:style="!isInvalid ? 'color: ' + value : null"
					class="k-color-field-preview k-color-preview"
				/>
			</template>

			<template v-if="currentOption?.text" #after>
				{{ currentOption.text }}
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
		onInput(input) {
			this.setPicker(input);
			this.$emit("input", input);
		},
		onPicker(hsv) {
			const input = this.convert(hsv);
			this.$emit("input", input);
		},
		onOption(option) {
			const value = this.convert(option.value);

			if (value !== this.value || this.required) {
				this.$emit("input", value);
			} else {
				this.$emit("input", "");
			}
		},
		setPicker(value = this.value) {
			if (this.$refs.color) {
				this.$refs.color.value = this.$library.colors.parseAs(value, "hsv");
			}
		}
	}
};
</script>

<style>
.k-color-field {
	--preview-width: 1.5rem;
}
.k-color-field .k-input .k-input-before {
	padding: 0.25rem;
}
.k-color-field .k-input .k-color-field-preview {
	width: calc(var(--field-input-height) - 0.5rem);
	height: calc(var(--field-input-height) - 0.5rem);
	flex-shrink: 0;
	transition: none;
}

.k-color-field-picker {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-3, 0.75rem);
	padding: var(--spacing-2, 0.5rem);
}

.k-color-field .k-color {
	width: 12rem;
}

.k-color-field-options {
	display: flex;
	justify-content: flex-start;
	flex-wrap: wrap;
	gap: var(--spacing-3, 0.75rem);
}

.k-dropdown .k-color-field-options {
	justify-content: space-around;
}

.k-color-field .k-color-preview {
	aspect-ratio: 1/1;
	width: var(--preview-width);
}

.k-color-field .k-color-preview[aria-current] {
	border: 2px solid var(--color-focus);
	outline: var(--field-input-focus-outline);
}

.k-color-field .k-color-input {
	font-size: var(--text-sm);
	font-family: var(--font-mono);
}

.k-color-field .k-input-after {
	font-size: var(--text-xs);
}
</style>
