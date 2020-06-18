import Field from "./Field.vue";
import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "UI | Form / Foundation / Field",
  component: Field,
  decorators: [Padding]
};

export const regular = () => ({
  template: `
    <k-field
      :input="_uid"
      label="My field"
    >
      <k-input
        :id="_uid"
        type="text"
        theme="field"
      />
    </k-field>
  `
});

export const required = () => ({
  template: `
    <k-field
      :input="_uid"
      :required="true"
      label="My field"
    >
      <k-input
        :id="_uid"
        :required="true"
        type="text"
        theme="field"
      />
    </k-field>
  `
});

export const disabled = () => ({
  template: `
    <k-field
      :input="_uid"
      :disabled="true"
      label="My field"
    >
      <k-input
        :id="_uid"
        :required="true"
        type="text"
        theme="field"
      />
    </k-field>
  `
});

export const counter = () => ({
  data() {
    return {
      value: ""
    };
  },
  template: `
    <k-field
      :counter="{
        count: value.length,
        max: 100,
        min: 10
      }"
      :input="_uid"
      :required="true"
      label="My field"
    >
      <k-input
        v-model="value"
        :id="_uid"
        :maxlength="100"
        :minlength="10"
        :required="true"
        type="text"
        theme="field"
      />
    </k-field>
  `
});

export const singleOption = () => ({
  template: `
    <k-field
      :input="_uid"
      :required="true"
      label="My field"
    >
      <template #options>
        <k-button icon="edit">Edit</k-button>
      </template>

      <k-input
        :id="_uid"
        :required="true"
        type="text"
        theme="field"
      />
    </k-field>
  `
});
