import Pagination from "./Pagination.vue";
import {
  withKnobs,
  text,
  select,
  number,
  boolean
} from '@storybook/addon-knobs';

export default {
  title: "Navigation / Pagination",
  decorators: [withKnobs],
  component: Pagination
};

export const configurator = () => ({
  template: '<k-pagination v-bind="$props" />',
  props: {
    align: {
      default: select('align', ['left', 'center', 'right'], 'left'),
    },
    details: {
      default: boolean('details', true),
    },
    dropdown: {
      default: boolean('dropdown', true),
    },
    limit: {
      default: number('limit', 5),
    },
    page: {
      default: number('page', 1),
    },
    nextLabel: {
      default: text('nextLabel')
    },
    prevLabel: {
      default: text('prevLabel')
    },
    total: {
      default: number('total', 20),
    }
  }
});

export const minimal = () => ({
  template: '<k-pagination :total="20" :limit="5" :details="false" />',
});

