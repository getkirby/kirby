<script>
import TextInput from "./TextInput.vue";

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
