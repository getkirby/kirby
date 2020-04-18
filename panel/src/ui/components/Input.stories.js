import Input from "./Input.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Form / Foundation / Input",
  component: Input,
  decorators: [Padding]
};

export const unstyled = () => ({
  template: `
    <k-input before="Text before the input" after="Text after the input" icon="edit">
      <input type="text" />
    </k-input>
  `
});

export const styledWithNativeInput = () => ({
  template: `
    <k-input theme="field" before="Text before the input" after="Text after the input" icon="edit">
      <input type="text" />
    </k-input>
  `
});

export const styledWithCustomInput = () => ({
  template: `
    <k-input theme="field" before="Text before the input" after="Text after the input" icon="edit">
      <k-text-input />
    </k-input>
  `
});

