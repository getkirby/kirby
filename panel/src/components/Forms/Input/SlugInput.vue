<template>
	<k-string-input
		v-bind="$props"
		:spellcheck="false"
		:value="slug"
		autocomplete="off"
		class="k-slug-input"
		@input="$emit('input', $event)"
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
	emits: ["input"],
	data() {
		return {
			slug: this.sluggify(this.value),
			slugs: this.$panel.language.rules ?? this.$panel.system.slugs,
			syncValue: null
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
				this.onInput(this.sluggify(this.syncValue));
			},
			deep: true,
			immediate: true
		},
		value: {
			handler(newValue) {
				newValue = this.sluggify(newValue);

				if (newValue !== this.slug) {
					this.slug = newValue;
					this.$emit("input", this.slug);
				}
			},
			immediate: true
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
		onInput(value) {
			this.slug = this.sluggify(value);
			this.$emit("input", this.slug);
		}
	}
};
</script>
