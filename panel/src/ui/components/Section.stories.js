import Section from "./Section.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Blueprints / Section",
  decorators: [Padding],
  component: Section
};

export const simple = () => ({
  template: `
    <k-section
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});

export const label = () => ({
  template: `
    <k-section
      label="My section"
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});

export const required = () => ({
  template: `
    <k-section
      :required="true"
      label="My section"
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});

export const singleOption = () => ({
  computed: {
    options() {
      return [
        { icon: "add", text: "Add" }
      ];
    }
  },
  template: `
    <k-section
      :options="options"
      :required="true"
      label="My section"
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});

export const multipleOptions = () => ({
  computed: {
    options() {
      return [
        { icon: "add", text: "Add" },
        { icon: "trash", text: "Delete" },
      ];
    }
  },
  template: `
    <k-section
      :options="options"
      :required="true"
      label="My section"
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});

export const link = () => ({
  computed: {
    options() {
      return [
        { icon: "add", text: "Add" },
        { icon: "trash", text: "Delete" },
      ];
    }
  },
  template: `
    <k-section
      :options="options"
      :required="true"
      label="My section"
      link="https://getkirby.com"
      name="example"
      type="example"
    >
      Section content
    </k-section>
  `
});
