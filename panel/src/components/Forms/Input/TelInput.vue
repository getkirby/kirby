<script>
import TextInput from "./TextInput.vue";
import { props as TextInputProps } from "./TextInput.vue";

export const props = {
	mixins: [TextInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "tel"
		},
		type: {
			type: String,
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
