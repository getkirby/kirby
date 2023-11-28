<script>
import TextInput, { props as TextInputProps } from "./TextInput.vue";

export const props = {
	mixins: [TextInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "email"
		},
		placeholder: {
			type: String,
			default: () => window.panel.$t("email.placeholder")
		},
		type: {
			type: String,
			default: "email"
		}
	}
};

/**
 * @example <k-email-input :value="email" @input="email = $event" name="email" />
 */
export default {
	extends: TextInput,
	mixins: [props]
};
</script>
