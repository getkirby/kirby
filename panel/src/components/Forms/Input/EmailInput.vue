<script>
import { TranslationString } from '@/config/i18n.js'
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
      type: [String, TranslationString],
      default() {
        return new TranslationString("email.placeholder")
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
