<script>
import {
	default as ChoiceInput,
	props as ChoiceInputProps
} from "./ChoiceInput.vue";

export const props = {
	mixins: [ChoiceInputProps],
	type: {
		default: "radio"
	}
};

/**
 *
 * @example <k-input :value="radio" @input="radio = $event" type="radio" />
 */
export default {
	mixins: [ChoiceInput, props]
};
</script>
