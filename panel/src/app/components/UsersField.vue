<script>
import PickerFieldFoundation from "@/ui/components/PickerFieldFoundation.vue";

// TODO: implement actual API instead
import { User, Users } from "../../../storybook/data/PickerItems.js";

export default {
  extends: PickerFieldFoundation,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "user",
          text: this.$t("field.users.empty")
        };
      }
    }
  },
  methods: {
    async getItems(ids) {
      return ids.map(id => User(id));
    },
    async getOptions({page, limit, parent, search}) {
      return Users(page, limit, parent, search);
    },
  }
}
</script>
