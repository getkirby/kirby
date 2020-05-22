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
  template: `
    <div>
      <k-files-field
        v-model="value"
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
      :sortable="false"
      label="Picker"
    />
  `
});

export const empty = () => ({
  extends: list(),
  template: `
    <k-files-field label="Files" />
  `
});

export const customEmpty = () => ({
  extends: list(),
  template: `
    <k-files-field
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
      :value="value"
      :hasOptions="false"
      label="Picker"
    />
  `
});

export const noUpload = () => ({
  extends: list(),
  template: `
    <k-files-field
      :value="value"
      :upload="false"
      label="Picker"
    />
  `
});

export const disabled = () => ({
  extends: list(),
  template: `
    <k-files-field
      :value="value"
      :disabled="true"
      label="Picker"
    />
  `
});
