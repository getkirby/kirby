<script>
import ModelsDialog from "./ModelsDialog.vue";

export default {
  extends: ModelsDialog,
  props: {
    endpoint: {
      type: String,
      // TODO: actual API endpoint
      default: "field/pages/options"
    }
  },
  computed: {
    type() {
      return "pages";
    }
  }
}
</script>
