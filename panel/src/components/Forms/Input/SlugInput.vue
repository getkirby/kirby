<template>
	<k-string-input
		v-bind="$props"
		:value="value"
		class="k-slug-input"
		type="text"
		@input="emit($event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			default: "off",
			type: String
		},
		allow: {
			type: String,
			default: ""
		},
		formData: {
			type: Object,
			default: () => ({})
		},
		spellcheck: {
			default: "false",
			type: String
		},
		sync: {
			type: String
		}
	}
};

/**
 * @example <k-slug-input :value="value" @input="value = $event" />
 * @public
 */
export default {
	mixins: [StringInput, props],
	data() {
		return {
			slugs: this.$panel.language.rules ?? this.$panel.system.slugs
		};
	},
	watch: {
		formData: {
			handler(newValue, oldValue = {}) {
				if (this.disabled || !this.sync || newValue[this.sync] === undefined) {
					return;
				}

				if (newValue[this.sync] === oldValue[this.sync]) {
					return;
				}

				this.emit(newValue[this.sync]);
			},
			deep: true,
			immediate: true
		},
		value: {
			handler(newValue, oldValue) {
				if (newValue === oldValue) {
					return;
				}

				this.emit(newValue);
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
		emit(value) {
			this.$emit("input", this.sluggify(value));
		}
	}
};
</script>
