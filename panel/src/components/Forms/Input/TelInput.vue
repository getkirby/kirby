<script>
import TextInput, { props as TextInputProps } from "./TextInput.vue";

export const props = {
	mixins: [TextInputProps],
	props: {
		autocomplete: {
			default: "tel"
		},
		placeholder: {
			default: () => window.panel.$t("tel.placeholder")
		},
		type: {
			default: "tel"
		}
	}
};

/**
 * @example <k-input :value="tel" @input="tel = $event" name="tel" type="tel" />
 */
export default {
	extends: TextInput,
	mixins: [props]
};
</script>
