import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Form / Field / Files Field",
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
        field: "field/files"
      };
    }
  },
  template: `
    <div>
      <k-files-field
        v-model="value"
        :endpoints="endpoints"
        label="Files"
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
      value: ["13", "72", "82", "2"]
    };
  },
  template: `
    <k-files-field
      v-model="value"
      :endpoints="endpoints"
      label="Files"
      layout="cardlets"
    />
  `
});

export const cards = () => ({
  extends: list(),
  data() {
    return {
      value: ["13", "72", "189"]
    };
  },
  template: `
    <k-files-field
      v-model="value"
      :endpoints="endpoints"
      label="Files"
      layout="cards"
    />
  `
});

export const pickerLayout = () => ({
  extends: list(),
  template: `
    <k-files-field
      v-model="value"
      label="Files"
      :endpoints="endpoints"
      :picker="{
        layout: 'cards',
        preview: { cover: true },
        width: 'large'
      }"
    />
  `
});

export const single = () => ({
  extends: list(),
  data() {
    return {
      value: ["42"]
    };
  },
  template: `
    <div>
      <k-files-field
        v-model="value"
        :endpoints="endpoints"
        :multiple="false"
        label="Picker"
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
    <k-files-field
      :endpoints="endpoints"
      :value="value"
      :max="3"
      label="Picker"
      help="Maximum 3 items allowed"
    />
  `
});

export const noSearch = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      :search="false"
      label="Picker"
    />
  `
});

export const nonSortable = () => ({
  extends: list(),
  template: `
    <k-files-field
      v-model="value"
      :endpoints="endpoints"
      :sortable="false"
      label="Picker"
    />
  `
});

export const empty = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      label="Files"
    />
  `
});

export const customEmpty = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      :empty="{ text: 'Add your favorite photos', icon: 'heart' }"
      label="Picker"
    />
  `
});

export const hasNoFiles = () => ({
  extends: list(),
  data() {
    return {
      value: []
    };
  },
  template: `
    <k-files-field
      :endpoints="endpoints"
      :value="value"
      :hasOptions="false"
      label="Picker"
    />
  `
});

export const noUploads = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      :value="value"
      :uploads="false"
      label="Picker"
    />
  `
});

export const uploadOnlyImages = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      :value="value"
      :uploads="{ accept: 'images/*' }"
      label="Picker"
    />
  `
});

export const disabled = () => ({
  extends: list(),
  template: `
    <k-files-field
      :endpoints="endpoints"
      :value="value"
      :disabled="true"
      label="Picker"
    />
  `
});
