<script>
import TextInput from "./TextInput.vue";

/**
 * @example <k-input v-model="tel" name="tel" type="tel" />
 */
export default {
  extends: TextInput,
  props: {
    ...TextInput.props,
    autocomplete: {
      type: String,
      default: "tel"
    },
    type: {
      type: String,
      default: "tel"
    }
  }
}
</script>
