import RadioInput from "./RadioInput.vue";

export default {
  title: "Form / Input / Radio Input",
  component: RadioInput
};

export const regular = () => ({
  data() {
    return {
      value: "",
    };
  },
  computed: {
    options() {
      return [
        { value: "a", text: "A" },
        { value: "b", text: "B" },
        { value: "c", text: "C" }
      ];
    }
  },
  template: `
    <div>
      <k-radio-input
        v-model="value"
        :options="options"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});


export const autofocus = () => ({
  ...regular(),
  template: `
    <div>
      <k-radio-input
        v-model="value"
        :autofocus="true"
        :options="options"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `,
});

export const disabled = () => ({
  ...regular(),
  template: `
    <div>
      <k-radio-input
        v-model="value"
        :disabled="true"
        :options="options"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

export const columns = () => ({
  ...regular(),
  computed: {
    columns() {
      return 3;
    },
    options: regular().computed.options
  },
  template: `
    <div>
      <k-radio-input
        v-model="value"
        :columns="columns"
        :options="options"
      />

      <br>
      <br>

      Value: {{ value }}
    </div>
  `
});

