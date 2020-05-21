<script>
import PickerField from "@/ui/components/PickerField.vue";

// TODO: implement actual API instead
import { Page, Pages } from "../../../storybook/data/PickerItems.js";

export default {
  extends: PickerField,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "page",
          text: this.$t("field.pages.empty")
        };
      }
    }
  },
  methods: {
    async getItems(ids) {
      return ids.map(id => Page(id));
      // TODO: actual singular API call
      // to support sorting through the backend
    },
    async getOptions({page, limit, parent, search}) {
      return Pages(page, limit, parent, search);
      // TODO: actual singular API call
    },
  }
}
</script>
