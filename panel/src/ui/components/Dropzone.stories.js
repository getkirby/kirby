import { action } from "@storybook/addon-actions";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Interaction / Dropzone",
  decorators: [Padding]
};

export const regular = () => ({
  methods: {
    drop: action("drop")
  },
  template: `
    <k-dropzone @drop="drop">Drop a file here</k-dropzone>
  `,
});

export const disabled = () => ({
  methods: {
    drop: action("drop")
  },
  template: `
    <k-dropzone :disabled="true" @drop="drop">Drop a file here</k-dropzone>
  `
});
