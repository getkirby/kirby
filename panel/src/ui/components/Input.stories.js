import Input from "./Input.vue";

export default {
  title: "Form / Foundation / Input",
  component: Input
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

