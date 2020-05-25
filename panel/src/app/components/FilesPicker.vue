<script>
import ModelsPicker from "./ModelsPicker.vue";

export default {
  extends: ModelsPicker,
  props: {
    endpoint: {
      type: String,
      // TODO: actual API endpoint
      default: "field/files/options"
    }
  }
}
</script>
