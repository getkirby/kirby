import Fieldset from "./Fieldset.vue";
import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Foundation / Fieldset",
  component: Fieldset,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      values: {}
    };
  },
  computed: {
    autofocus() {
      return false;
    },
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        email: {
          label: "Email",
          type: "email",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "textarea"
        }
      };
    }
  },
  methods: {
    input: action("input"),
    focus: action("focus"),
    submit: action("submit")
  },
  template: `
    <div>
      <k-fieldset
        :autofocus="autofocus"
        :fields="fields"
        v-model="values"
        class="mb-8"
        @focus="focus"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

export const autofocus = () => ({
  extends: regular(),
  computed: {
    autofocus() {
      return true;
    },
  }
});

export const biDirectional = () => ({
  extends: regular(),
  template: `
    <div>
      <k-grid gutter="large" class="mb-8">
        <k-column width="1/2">
          <k-fieldset
            :fields="fields"
            v-model="values"
            @focus="focus"
            @input="input"
            @submit="submit"
          />
        </k-column>
        <k-column width="1/2">
          <k-fieldset
            :fields="fields"
            v-model="values"
            @focus="focus"
            @input="input"
            @submit="submit"
          />
        </k-column>
      </k-grid>

      <k-headline class="mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});
