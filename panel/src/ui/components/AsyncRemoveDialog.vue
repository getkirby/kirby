<script>
import AsyncDialog from "./AsyncDialog.vue";

export default {
  extends: AsyncDialog,
  data() {
    return {
      submitButton: {
        color: "red",
        icon: "trash",
        text: this.$t("delete")
      }
    }
  }
};
</script>
