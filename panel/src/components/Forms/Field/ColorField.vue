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
						<button
							:style="!isInvalid ? 'color: ' + value : null"
							class="k-color-field-preview k-color-preview"
							type="button"
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
									:aria-current="color.value === currentOption?.value"
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

export default {
	mixins: [Field, Input, ColornameInput],
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
.k-color-field .k-input .k-input-before {
	align-items: center;
	padding-inline: var(--spacing-1);
}
.k-color-field .k-color-field-preview {
	--color-preview-size: calc(var(--input-height) - var(--spacing-2));
}

.k-color-field-picker {
	padding: var(--spacing-2);
}
.k-color-field .k-color {
	width: 12rem;
}
.k-color-field .k-color > *:first-child {
	border-radius: var(--rounded-sm);
}
</style>
