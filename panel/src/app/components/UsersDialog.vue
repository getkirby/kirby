<script>
import ModelsDialog from "./ModelsDialog.vue";

export default {
  extends: ModelsDialog,
  props: {
    options: {
      type: Function,
      async default(params) {
        // TODO: actual API endpoint
        return this.$api.get("field/users/options", params);
      }
    }
  },
  computed: {
    type() {
      return "users";
    }
  }
}
</script>
