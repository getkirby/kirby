<script>
import Dialog from "@/ui/components/Navigation/Dialog.vue";

export default {
  extends: Dialog,
  created() {
    this.$events.$on("key.escape", this.close, false);
  },
  destroyed() {
    this.$events.$off("key.escape", this.close, false);
  }
};
</script>
