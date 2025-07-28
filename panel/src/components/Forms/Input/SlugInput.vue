<template>
	<k-string-input
		v-bind="$props"
		:spellcheck="false"
		:value="displayValue"
		autocomplete="off"
		class="k-slug-input"
		@input="onInput"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		// unset unused props
		autocomplete: null,
		spellcheck: null,

		/**
		 * Allow only specific characters for slug generation
		 */
		allow: {
			type: String,
			default: ""
		},
		/**
		 * Values of other form inputs available for slug generation
		 */
		formData: {
			type: Object,
			default: () => ({})
		},
		/**
		 * Name of the input to generate the slug from
		 */
		sync: {
			type: String
		}
	}
};

/**
 * @example <k-input :value="slug" @input="slug = $event" name="slug" type="slug" />
 */
export default {
	extends: StringInput,
	mixins: [props],
	data() {
		return {
			displayValue: this.value,
			slugs: this.$panel.language.rules ?? this.$panel.system.slugs,
			syncValue: null,
			slugifyTimeout: null
		};
	},
	watch: {
		formData: {
			handler(newValue) {
				if (this.disabled) {
					return false;
				}

				if (!this.sync || newValue[this.sync] === undefined) {
					return false;
				}

				if (newValue[this.sync] == this.syncValue) {
					return false;
				}

				this.syncValue = newValue[this.sync];
				this.onInput(this.syncValue, true);
			},
			deep: true,
			immediate: true
		},
		value(newValue) {
			if (newValue !== this.displayValue) {
				this.displayValue = newValue;
			}
		}
	},
	beforeDestroy() {
		if (this.slugifyTimeout) {
			clearTimeout(this.slugifyTimeout);
		}
	},
	methods: {
		sluggify(value) {
			return this.$helper.slug(
				value,
				[this.slugs, this.$panel.system.ascii],
				this.allow
			);
		},
		onInput(value, immediate = false) {
			// Update display value immediately to prevent UI lag
			this.displayValue = value;
			
			// Clear any existing timeout
			if (this.slugifyTimeout) {
				clearTimeout(this.slugifyTimeout);
			}
			
			// Debounce the slugification to prevent premature character stripping
			const delay = immediate ? 0 : 300;
			
			this.slugifyTimeout = setTimeout(() => {
				const sluggified = this.sluggify(value);
				
				// Only update if the sluggified value is different
				if (sluggified !== value) {
					this.displayValue = sluggified;
				}
				
				this.$emit("input", sluggified);
			}, delay);
		}
	}
};
</script>
