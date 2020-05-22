import { action } from "@storybook/addon-actions";
import FilePreview from "./FilePreview.vue";

export default {
  title: "UI | Layout / File Preview",
  component: FilePreview,
};

export const imageOnly = () => ({
  template: `
    <k-file-preview
      image="https://source.unsplash.com/user/erondu/1600x900"
    />
  `
});

export const withInfo = () => ({
  template: `
    <k-file-preview
      height="400"
      image="https://source.unsplash.com/user/erondu/1600x900"
      link="https://getkirby.com"
      mime="image/jpeg"
      orientation="landscape"
      size="1234 kb"
      template="cover"
      width="1200"
    />
  `
});

export const withIcon = () => ({
  template: `
    <k-file-preview
      :icon="{ type: 'image' }"
      height="400"
      link="https://getkirby.com"
      mime="image/jpeg"
      orientation="landscape"
      size="1234 kb"
      template="cover"
      width="1200"
    />
  `
});

export const withIconColor = () => ({
  template: `
    <k-file-preview
      :icon="{
        type: 'image',
        color: 'green-light'
      }"
      height="400"
      link="https://getkirby.com"
      mime="image/jpeg"
      orientation="landscape"
      size="1234 kb"
      template="cover"
      width="1200"
    />
  `
});
