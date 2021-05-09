<script>
import TextInput from "./TextInput.vue";
import { props as TextInputProps } from "./TextInput.vue";

export const props = {
  mixins: [TextInputProps],
  props: {
    autocomplete: {
      type: String,
      default: "email"
    },
    placeholder: {
      type: String,
      default() {
        // TODO: $t('email.placeholer')
        return 'email.placeholer';
      }
    },
    type: {
      type: String,
      default: "email"
    }
  }
}

/**
 * @example <k-input v-model="email" type="email" name="email" />
 */
export default {
  extends: TextInput,
  mixins: [props]
}
</script>
