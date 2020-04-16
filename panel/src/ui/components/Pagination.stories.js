import Pagination from "./Pagination.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Navigation / Pagination",
  decorators: [Padding],
  component: Pagination
};

export const regular = () => ({
  template: `
    <k-pagination
      :page="1"
      :total="20"
    />
  `,
});

export const details = () => ({
  template: `
    <k-pagination
      :details="true"
      :page="1"
      :total="20"
    />
  `
});

export const center = () => ({
  template: `
    <k-pagination
      :details="true"
      :page="1"
      :total="20"
      align="center"
    />
  `
});

export const right = () => ({
  template: `
    <k-pagination
      :details="true"
      :page="1"
      :total="20"
      align="right"
    />
  `
});

export const disabledDropdown = () => ({
  template: `
    <k-pagination
      :details="true"
      :dropdown="false"
      :page="1"
      :total="20"
    />
  `
});

