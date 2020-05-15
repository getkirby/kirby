import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "Lab | Sections",
  decorators: [Padding],
};

export const PagesSection = () => ({

  template: `
    <k-pages-section
      label="Pages"
      :add="true"
      @add="$refs.addDialog.open()"
    >

      <k-form-dialog ref="addDialog" />

    </k-pages-section>
  `
});
