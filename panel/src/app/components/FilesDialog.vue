<script>
import ModelsDialog from "./ModelsDialog.vue";

export default {
  extends: ModelsDialog,
  props: {
    endpoint: {
      type: String,
      // TODO: actual API endpoint
      default: "field/files/options"
    }
  },
  computed: {
    type() {
      return "files";
    }
  }
}
</script>
