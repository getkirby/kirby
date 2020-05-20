import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Layout / Header Bar",
  decorators: [Padding]
};

export const simple = () => ({
  template: `
    <k-header-bar text="Pages" />
  `,
});

export const singleOption = () => ({
  computed: {
    options() {
      return [
        { icon: "add", text: "Add", option: "add" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      text="Pages"
    />
  `,
});

export const multipleOptions = () => ({
  computed: {
    options() {
      return [
        { icon: "upload", text: "Upload", option: "upload" },
        { icon: "check", text: "Select", option: "select" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      text="Pages"
    />
  `,
});

export const optionsIconAndText = () => ({
  computed: {
    options() {
      return [
        { icon: "upload", text: "Upload", option: "upload" },
        { icon: "check", text: "Select", option: "select" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      options-icon="add"
      options-text="Add"
      text="Pages"
    />
  `,
});

export const linkAndOptions = () => ({
  computed: {
    options() {
      return [
        { icon: "upload", text: "Upload", option: "upload" },
        { icon: "check", text: "Select", option: "select" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      link="https://getkirby.com"
      options-icon="add"
      options-text="Add"
      text="Pages"
    />
  `,
});

export const required = () => ({
  computed: {
    options() {
      return [
        { icon: "upload", text: "Upload", option: "upload" },
        { icon: "check", text: "Select", option: "select" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      :required="true"
      link="https://getkirby.com"
      options-icon="add"
      options-text="Add"
      text="Pages"
    />
  `,
});

export const labelFor = () => ({
  computed: {
    options() {
      return [
        { icon: "upload", text: "Upload", option: "upload" },
        { icon: "check", text: "Select", option: "select" },
      ];
    }
  },
  template: `
    <k-header-bar
      :options="options"
      :required="true"
      element="label"
      for="my-input"
      options-icon="add"
      options-text="Add"
      text="Pages"
    />
  `,
});
