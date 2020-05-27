import Padding from "../../../storybook/theme/Padding.js";
import { action } from "@storybook/addon-actions";
import { complex } from "./Form.stories.js";
import StructureField from "./StructureField.vue";

export default {
  title: "UI | Form / Field / Structure Field",
  component: StructureField,
  decorators: [Padding]
};

export const regular = () => ({
  data() {
    return {
      value: [
        { platform: "Twitter", url: "https://twitter.com/getkirby" },
        { platform: "Instagram", url: "https://instagram.com/getkirby" },
        { platform: "Github", url: "https://github.com/getkirby" }
      ]
    };
  },
  computed: {
    columns() {
      return {
        platform: {
          label: "Platform"
        },
        url: {
          label: "URL",
          type: "url"
        }
      };
    },
    fields() {
      return {
        platform: {
          label: "Platform",
          type: "text",
          width: "1/2"
        },
        url: {
          label: "URL",
          type: "url",
          width: "1/2"
        },
      };
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        label="Social"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const manyFields = () => ({
  data() {
    return {
      value: [
        { range: 50000, select: "Design" },
        { range: 2000, select: "Photography" }
      ]
    };
  },
  computed: {
    columns() {
      return {
        select: {
          label: "Area"
        },
        range: {
          label: "Budget",
          type: "number",
          before: "$"
        }
      };
    },
    fields() {
      return complex().computed.fields();
    }
  },
  methods: {
    input: action("input")
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        label="Complex from with many fields"
        class="mb-8"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `,
});

export const disabledSorting = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        :sortable="false"
        class="mb-8"
        label="Social"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const max = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        :max="4"
        class="mb-8"
        help="There's a maximum of four rows"
        label="Social"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const sortBy = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        class="mb-8"
        label="Social"
        sortBy="platform asc"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const disabled = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        :disabled="true"
        class="mb-8"
        label="Social"
        @input="input"
      />
    </div>
  `
});

export const pagination = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        :limit="2"
        class="mb-8"
        label="Social"
        @input="input"
      />

      <k-headline class="mb-3">Value</k-headline>
      <k-code-block :code="value" />
    </div>
  `
});

export const prepend = () => ({
  extends: regular(),
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        :prepend="true"
        class="mb-8"
        label="Social"
        @input="input"
      />
    </div>
  `
});

export const empty = () => ({
  extends: regular(),
  data() {
    return {
      value: []
    };
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        class="mb-8"
        label="Social"
        @input="input"
      />
    </div>
  `
});

export const customEmptyMessage = () => ({
  extends: regular(),
  data() {
    return {
      value: []
    };
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        class="mb-8"
        empty="No social media accounts yet"
        label="Social"
        @input="input"
      />
    </div>
  `
});

export const smallDrawer = () => ({
  extends: regular(),
  computed: {
    fields() {
      let fields = regular().computed.fields();
      Object.keys(fields).forEach(field => {
        delete fields[field].width;
      });
      return fields;
    }
  },
  template: `
    <div>
      <k-structure-field
        v-model="value"
        :columns="columns"
        :fields="fields"
        class="mb-8"
        label="Social"
        size="small"
        @input="input"
      />
    </div>
  `
});
