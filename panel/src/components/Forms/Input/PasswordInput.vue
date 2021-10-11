<script>
import TextInput from "./TextInput.vue";
import { props as TextInputProps } from "./TextInput.vue";

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
 * @example <k-input v-model="password" name="password" type="password" />
 */
export default {
  extends: TextInput,
  mixins: [props]
};
</script>
