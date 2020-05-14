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
    placeholder: {
      type: String,
      default() {
        return this.$t("email.placeholder");
      }
    },
    trim: {
      type: Boolean,
      default: true,
    },
    type: {
      type: String,
      default: "email"
    }
  }
}
</script>
