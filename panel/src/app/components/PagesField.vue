<script>
import PickerFieldFoundation from "@/app/components/PickerFieldFoundation.vue";

// TODO: implement actual API instead
import { Page, Pages } from "../../../storybook/data/PickerItems.js";

export default {
  extends: PickerFieldFoundation,
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
    },
  }
}
</script>
