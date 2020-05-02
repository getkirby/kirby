<script>
import PickerField from "./PickerField.vue";

// TODO: implement actual API instead
import { File, Files } from "../storybook/PickerItems.js";

export default {
  extends: PickerField,
  props: {
    empty: {
      type: [String, Object],
      default() {
        return {
          icon: "file",
          text: this.$t("field.files.empty")
        };
      }
    }
  },
  methods: {
    async getItems(ids) {
      return ids.map(id => File(id));
    },
    async getOptions({page, limit, parent, search}) {
      return Files(page, limit, parent, search);
    },
  }
}
</script>
