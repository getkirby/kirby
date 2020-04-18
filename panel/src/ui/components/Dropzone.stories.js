import Dropzone from "./Dropzone.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Interaction / Dropzone",
  component: Dropzone,
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


