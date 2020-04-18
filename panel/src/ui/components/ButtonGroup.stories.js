import ButtonGroup from "./ButtonGroup.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Navigation / Button Group",
  component: ButtonGroup,
};

export const regular = () => ({
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
