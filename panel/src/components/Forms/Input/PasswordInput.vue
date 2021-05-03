<script>
import TextInput from "./TextInput.vue";

/**
 * @example <k-input v-model="password" name="password" type="password" />
 */
export default {
  extends: TextInput,
  props: {
    ...TextInput.props,
    autocomplete: {
      type: String,
      default: "new-password"
    },
    type: {
      type: String,
      default: "password"
    }
  }
}
</script>
