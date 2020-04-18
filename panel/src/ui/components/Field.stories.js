import Field from "./Field.vue";
import Padding from "../storybook/Padding.js";

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

export const options = () => ({
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


