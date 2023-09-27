<template>
	<k-string-input
		v-bind="$props"
		class="k-colorname-input"
		type="text"
		@blur.native="onBlur"
		@input="$emit('input', $event)"
		@paste.native="onPaste"
		@keydown.native.meta.s.stop.prevent="onSave"
		@keydown.native.enter="onSave"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		/**
		 * Add the alpha value to the color name
		 */
		alpha: {
			type: Boolean,
			default: true
		},
		autocomplete: {
			default: "off",
			type: String
		},
		/**
		 * @values `hex`, `rgb`, `hsl`
		 */
		format: {
			type: String,
			default: "hex",
			validator: (format) => ["hex", "rgb", "hsl"].includes(format)
		},
		spellcheck: {
			default: "false",
			type: String
		}
	}
};

/**
 * @example <k-colorname-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props],
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

			try {
				return this.$library.colors.toString(value, this.format, this.alpha);
			} catch (e) {
				return value;
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
