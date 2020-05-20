import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Button Group",
  decorators: [Padding]
};

export const example = () => ({
  methods: {
    edit: action('edit'),
    remove: action('remove')
  },
  template: `
    <k-button-group>
      <k-button icon="edit" @click="edit">Edit</k-button>
      <k-button icon="trash" @click="remove">Remove</k-button>
    </k-button-group>
  `,
});
