import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Form / Field / Users Field",
  decorators: [Padding]
};

export const list = () => ({
  data() {
    return {
      value: ["13", "72"]
    };
  },
  computed: {
    endpoints() {
      return {
        // TODO: actual fake API endpoint
        field: "field/users"
      };
    }
  },
  template: `
    <div>
      <k-users-field
        v-model="value"
        :endpoints="endpoints"
        label="Users"
      />

      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const cardlets = () => ({
  extends: list(),
  data() {
    return {
      value: ["13", "72", "76", "99"]
    };
  },
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      label="Users"
      layout="cardlets"
    />
  `
});

export const cards = () => ({
  extends: list(),
  data() {
    return {
      value: ["13", "72", "1"]
    };
  },
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      label="Users"
      layout="cards"
    />
  `
});

export const pickerLayout = () => ({
  extends: list(),
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      label="Users"
      layout="cardlets"
      :picker="{
        layout: 'list'
      }"
    />
  `
});

export const single = () => ({
  extends: list(),
  data() {
    return {
      value: ["42"]
    }
  },
  template: `
    <div>
      <k-users-field
        v-model="value"
        :endpoints="endpoints"
        :multiple="false"
        label="Users"
        help="Only one items allowed"
      />

      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const max = () => ({
  extends: list(),
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      :max="3"
      label="Users"
      help="Maximum 3 items allowed"
    />
  `
});

export const noSearch = () => ({
  extends: list(),
  template: `
    <k-users-field
      :endpoints="endpoints"
      :search="false"
      label="Users"
    />
  `
});

export const nonSortable = () => ({
  extends: list(),
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      :sortable="false"
      label="Users"
    />
  `
});

export const empty = () => ({
  extends: list(),
  template: `
    <k-users-field
      :endpoints="endpoints"
      label="Users"
    />
  `
});

export const customEmpty = () => ({
  extends: list(),
  template: `
    <k-users-field
      :endpoints="endpoints"
      :empty="{ text: 'Select the employee of the month', icon: 'star' }"
      :multiple="false"
      label="Highlights"
    />
  `
});

export const hasNoUsers = () => ({
  extends: list(),
  data() {
    return {
      value: []
    };
  },
  template: `
    <k-users-field
      :endpoints="endpoints"
      :value="value"
      :hasOptions="false"
      label="Picker"
    />
  `
});

export const disabled = () => ({
  extends: list(),
  template: `
    <k-users-field
      v-model="value"
      :endpoints="endpoints"
      :disabled="true"
      label="Users"
    />
  `
});
