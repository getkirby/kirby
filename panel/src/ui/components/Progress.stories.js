import {
  withKnobs,
  number
} from '@storybook/addon-knobs';

export default {
  title: "Progress",
  decorators: [withKnobs]
};

export const configurator = () => ({
  template: '<k-progress :value="value" />',
  props: {
    value: {
      default: number('value', 0, {
        range: true,
        min: 0,
        max: 100
      }),
    }
  }
});

export const setValue = () => ({
  template: `
    <div>
      <k-progress ref="progress" :value="value" />

      <k-button-group>
        <k-button @click="$refs.progress.set(0)">0%</k-button>
        <k-button @click="$refs.progress.set(50)">50%</k-button>
        <k-button @click="$refs.progress.set(100)">100%</k-button>
      </k-button-group>
    </div>
  `,
});

