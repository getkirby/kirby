<script>
import TextInput, { props as TextInputProps } from "./TextInput.vue";

export const props = {
	mixins: [TextInputProps],
	props: {
		autocomplete: {
			type: String,
			default: "new-password"
		},
		type: {
			type: String,
			default: "password"
		}
	}
};

/**
 * @example <k-input :value="password" @input="password = $event" name="password" type="password" />
 */
export default {
	extends: TextInput,
	mixins: [props]
};
</script>
