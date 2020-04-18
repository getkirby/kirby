import Link from "./Link.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "UI | Navigation / Link",
  decorators: [Padding],
  component: Link
};

export const regular = () => ({
  template: '<k-link to="https://getkirby.com">https://getkirby.com</k-link>',
});

export const underlined = () => ({
  template: '<k-link class="underline" to="https://getkirby.com">https://getkirby.com</k-link>'
});

export const title = () => ({
  template: '<k-link title="Kirby Website" to="https://getkirby.com">https://getkirby.com</k-link>'
});

export const disabled = () => ({
  template: '<k-link :disabled="true" to="https://getkirby.com">Disabled link</k-link>',
});

export const withoutURL = () => ({
  template: '<k-link>No href attribute</k-link>',
});

export const targetBlank = () => ({
  template: '<k-link to="https://getkirby.com" target="_blank">Opens in a new tab</k-link>',
});

export const rel = () => ({
  template: '<k-link rel="me" to="https://getkirby.com">https://getkirby.com</k-link>'
});

