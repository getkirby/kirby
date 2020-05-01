<script>
import PickerField from "./PickerField.vue";

export default {
  extends: PickerField,
  computed: {
    model() {
      return "users";
    }
  }
}
</script>
