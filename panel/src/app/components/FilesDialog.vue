<script>
import ModelsDialog from "./ModelsDialog.vue";

export default {
  extends: ModelsDialog,
  props: {
    options: {
      type: Function,
      async default(params) {
        // TODO: actual API endpoint
        return this.$api.get("field/files/options", params);
      }
    }
  },
  computed: {
    type() {
      return "files";
    }
  }
}
</script>
