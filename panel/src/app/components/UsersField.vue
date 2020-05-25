<script>
import ModelsField from "@/app/components/ModelsField.vue";

export default {
  extends: ModelsField,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "user",
          text: this.$t("field.users.empty")
        };
      }
    },
    type: {
      type: String,
      default: "users"
    }
  }
}
</script>
