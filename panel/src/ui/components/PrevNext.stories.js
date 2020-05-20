import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Navigation / PrevNext",
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
  template: '<k-prev-next :prev="prev" />'
});

export const disabled = () => ({
  template: '<k-prev-next />'
});

export const vertical = () => ({
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
  template: '<k-prev-next :prev="prev" :next="next" direction="vertical" />'
});
