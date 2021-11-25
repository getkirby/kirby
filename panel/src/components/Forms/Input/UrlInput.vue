<script>
import TextInput from "./TextInput.vue";
import { props as TextInputProps } from "./TextInput.vue";

export const props = {
  mixins: [TextInputProps],
  props: {
    autocomplete: {
      type: String,
      default: "url"
    },
    type: {
      type: String,
      default: "url"
    }
  }
};

/**
 * @example <k-input v-model="url" name="url" type="url" />
 */
export default {
  extends: TextInput,
  mixins: [props]
};
</script>
