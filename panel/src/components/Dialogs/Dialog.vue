<script>
import Dialog from "@/ui/components/Navigation/Dialog.vue";

export default {
  extends: Dialog,
  created() {
    this.$events.$on("keydown.esc", this.close, false);
  },
  destroyed() {
    this.$events.$off("keydown.esc", this.close, false);
  }
};
</script>
