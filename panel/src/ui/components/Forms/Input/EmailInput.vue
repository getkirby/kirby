<script>
import TextInput from "./TextInput.vue";

export default {
  extends: TextInput,
  props: {
    ...TextInput.props,
    autocomplete: {
      type: String,
      default: "email"
    },
    type: {
      type: String,
      default: "email"
    }
  }
}
</script>
