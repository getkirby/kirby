<template>
	<k-string-input
		v-bind="$props"
		:spellcheck="false"
		autocomplete="off"
		class="k-colorname-input"
		type="text"
		@blur="onBlur"
		@input="$emit('input', $event)"
		@paste="onPaste"
		@keydown.meta.s.stop.prevent="onSave"
		@keydown.enter="onSave"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		// unset props
		autocomplete: null,
		font: null,
		maxlength: null,
		minlength: null,
		pattern: null,
		spellcheck: null,

		/**
		 * Add the alpha value to the color name
		 */
		alpha: {
			type: Boolean,
			default: true
		},
		/**
		 * @values "hex", "rgb", "hsl"
		 */
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		}
	}
};

/**
 * @since 4.0.0
 * @example <k-colorname-input :value="value" @input="value = $event" />
 */
export default {
	mixins: [StringInput, props],
	emits: ["input"],
	watch: {
		value() {
			this.validate();
		}
	},
	mounted() {
		this.validate();
	},
	methods: {
		convert(value) {
			if (!value) {
				return value;
			}

			try {
				// first try to parse the color via the library
				return this.$library.colors.toString(value, this.format, this.alpha);
			} catch {
				// if that fails,
				// create a new secret tester
				const test = document.createElement("div");

				// set the text color
				test.style.color = value;

				// it has to be in the document to work
				document.body.append(test);

				// check the computed style for a usable rgb value
				value = window.getComputedStyle(test).color;

				// remove the element
				test.remove();

				// as we always get a valid rgb value, we can safely
				// pass it to the library to get a valid color string
				// in the target format without additional fallback
				// (getComputedStyle will return rgb(0,0,0) for invalid colors)
				return this.$library.colors.toString(value, this.format, this.alpha);
			}
		},
		convertAndEmit(value) {
			this.emit(this.convert(value));
		},
		emit(value) {
			this.$emit("input", value);
		},
		onBlur() {
			this.convertAndEmit(this.value);
		},
		onPaste(input) {
			if (input instanceof ClipboardEvent) {
				input = this.$helper.clipboard.read(input, true);
			}

			this.convertAndEmit(input);
		},
		async onSave() {
			this.convertAndEmit(this.value);
			await this.$nextTick();
			this.$el.form?.requestSubmit();
		},
		validate() {
			let error = "";

			if (this.$library.colors.parse(this.value) === null) {
				error = this.$t("error.validation.color", {
					format: this.format
				});
			}

			this.$el.setCustomValidity(error);
		}
	}
};
</script>
