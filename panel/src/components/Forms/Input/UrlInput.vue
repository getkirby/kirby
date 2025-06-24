<template>
	<k-string-input
		v-bind="$props"
		type="url"
		class="k-url-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import StringInput, { props as StringInputProps } from "./StringInput.vue";

export const props = {
	mixins: [StringInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "url"
		},
		placeholder: {
			type: String,
			default: () => window.panel.t("url.placeholder")
		}
	}
};

/**
 * @example <k-input :value="url" @input="url = $event" name="url" type="url" />
 */
export default {
	mixins: [StringInput, props],
	watch: {
		value: {
			handler() {
				this.validate();
			},
			immediate: true
		}
	},
	methods: {
		validate() {
			const errors = [];

			// use custom stricter URL validation as the
			// default HTML5 validation is too permissive
			if (this.value && this.$helper.url.isUrl(this.value, true) === false) {
				errors.push(this.$t("error.validation.url"));
			}

			this.$el?.setCustomValidity(errors.join(", "));
		}
	}
};
</script>
