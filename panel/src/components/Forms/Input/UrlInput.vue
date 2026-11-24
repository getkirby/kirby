<template>
	<k-string-input
		v-bind="$props"
		:class="['k-url-input', $attrs.class]"
		:style="$attrs.style"
		type="url"
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
 * @example
 * <k-input :value="url" @input="url = $event" name="url" type="url" />
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
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
