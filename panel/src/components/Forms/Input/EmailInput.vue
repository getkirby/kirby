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
        return this.$t("email.placeholder");
      }
    },
    type: {
      type: String,
      default: "email"
    }
  }
}

export default {
  extends: TextInput,
  mixins: [props]
}
</script>
