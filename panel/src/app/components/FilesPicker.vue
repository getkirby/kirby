<script>
import ModelsPicker from "./ModelsPicker.vue";

export default {
  extends: ModelsPicker,
  props: {
    options: {
      type: Function,
      async default(params) {
        // TODO: actual API endpoint
        return this.$api.get("field/files/options", params);
      }
    }
  },
}
</script>
