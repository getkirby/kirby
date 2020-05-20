import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Notification",
  decorators: [Padding]
};

export const regular = () => ({
  methods: {
    onClose: action("close")
  },
  template: `
    <k-notification
      message="Just a message"
      @close="onClose"
    />
  `,
});

export const error = () => ({
  methods: {
    onClose: action("close")
  },
  template: `
    <k-notification
      message="An error message"
      type="error"
      @close="onClose"
    />
  `,
});

export const success = () => ({
  methods: {
    onClose: action("close")
  },
  template: `
    <k-notification
      message="A success message"
      type="success"
      @close="onClose"
    />
  `,
});
