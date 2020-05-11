<script>
import Dialog from "./Dialog.vue";

export default {
  extends: Dialog,
  props: {
    submitButton: {
      type: [String, Boolean],
      default() {
        return {
          icon: "trash",
          text: this.$t("delete"),
          color: "red"
        }
      }
    }
  }
}
</script>
