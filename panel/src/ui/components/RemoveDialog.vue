<script>
import Dialog from "./Dialog.vue";

export default {
  extends: Dialog,
  props: {
    icon: {
      type: String,
      default: "trash"
    },
    submitButton: {
      type: [String, Boolean],
      default() {
        return this.$t("delete");
      }
    },
    theme: {
      type: String,
      default: "negative"
    }
  }
}
</script>
