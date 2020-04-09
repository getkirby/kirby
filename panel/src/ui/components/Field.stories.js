import Field from "./Field.vue";

export default {
  title: "Form / Foundation / Field",
  component: Field
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

