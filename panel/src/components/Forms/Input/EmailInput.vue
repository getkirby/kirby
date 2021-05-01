<script>
import TextInput from "./TextInput.vue";

/**
 * @example <k-input v-model="email" type="email" name="email" />
 */
export default {
  extends: TextInput,
  props: {
    ...TextInput.props,
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
</script>
