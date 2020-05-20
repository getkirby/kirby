import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / Pagination",
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-pagination
      :page="1"
      :total="20"
    />
  `
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

export const singlePage = () => ({
  template: `
    <k-pagination
      :details="true"
      :page="1"
      :limit="1"
      :total="5"
    />
  `
});

export const zeroEntries = () => ({
  template: `
    <k-pagination
      :details="true"
      :page="1"
      :limit="1"
      :total="0"
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
