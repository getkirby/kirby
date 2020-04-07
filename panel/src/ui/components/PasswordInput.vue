<script>
import TextInput from "./TextInput.vue";

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
