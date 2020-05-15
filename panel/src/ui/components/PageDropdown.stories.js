import PageDropdown from "./PageDropdown.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Dropdown / Page Dropdown",
  decorators: [Padding],
  component: PageDropdown
};

export const regular = () => ({
  data() {
    return {
      page: 1,
      pages: 50,
    };
  },
  methods: {
    onChange(page) {
      action("change")(page);
      this.page = page;
    }
  },
  template: `
    <k-page-dropdown
      :page="page"
      :pages="pages"
      :text="'Page: ' + page + ' / ' + pages"
      @change="onChange"
    />
  `
});
