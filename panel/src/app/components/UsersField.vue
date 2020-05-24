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
    items: {
      type: Function,
      async default(ids) {
        const params = { ids: JSON.stringify(this.selected) };
        // TODO: actual API endpoint
        return this.$api.get("field/users/items", params);
      },
    },
    type: {
      type: String,
      default: "users"
    }
  }
}
</script>
