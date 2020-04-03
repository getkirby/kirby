import { withKnobs, number, select } from '@storybook/addon-knobs';

export default {
  title: "Grid",
  decorators: [withKnobs]
};

export const configurator = () => ({
  props: {
    columns: {
      default: number("columns", 12)
    },
    width: {
      default: select("column width", ["1/12", "1/6", "1/4", "1/3", "1/2", "1/1"], "1/12")
    },
    gutter: {
      default: select("gutter", ["none", "small", "medium", "large", "huge"], "small")
    },
  },
  template: `
    <k-grid :gutter="gutter">
      <k-column style="background: var(--color-positive-light); height: 6rem; padding: .5rem" v-for="n in columns" :key="width + '-' + n" :width="width">{{ n }}</k-column>
    </k-grid>
  `,
});

