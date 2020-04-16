import PrevNext from "./PrevNext.vue";
import Padding from "../storybook/Padding.js";

export default {
  title: "Navigation / PrevNext",
  component: PrevNext,
  decorators: [Padding]
};

export const enabled = () => ({
  data() {
    return {
      prev: {
        link: "https://getkirby.com"
      },
      next: {
        link: "https://getkirby.com"
      }
    };
  },
  template: '<k-prev-next :prev="prev" :next="next" />'
});

export const enabledDisabled = () => ({
  data() {
    return {
      prev: {
        link: "https://getkirby.com"
      }
    };
  },
  template: '<k-prev-next :prev="prev" :next="next" />'
});

export const disabled = () => ({
  template: '<k-prev-next />'
});

