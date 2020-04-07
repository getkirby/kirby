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
    placeholder: {
      type: String,
      default() {
        return this.$t("url.placeholder");
      }
    },
    type: {
      type: String,
      default: "url"
    }
  }
}
</script>
