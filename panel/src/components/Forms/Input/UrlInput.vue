<script>
import TextInput from "./TextInput.vue";

/**
 * @example <k-input v-model="url" name="url" type="url" />
 */
export default {
  extends: TextInput,
  props: {
    ...TextInput.props,
    autocomplete: {
      type: String,
      default: "url"
    },
    type: {
      type: String,
      default: "url"
    }
  }
}
</script>
