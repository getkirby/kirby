import Dropzone from "./Dropzone.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "Interaction / Dropzone",
  component: Dropzone
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


