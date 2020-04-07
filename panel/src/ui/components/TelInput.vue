<script>
import TextInput from "./TextInput.vue";

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
