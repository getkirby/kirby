import Padding from "../../../storybook/theme/Padding.js";

export default {
  title: "App | Interaction / Files Dialog",
  decorators: [Padding]
};

export const single = () => ({
  data() {
    return {
      value: ["4"]
    };
  },
  computed: {
    layout() {
      return "list";
    },
    max() {
      return;
    },
    multiple() {
      return false;
    },
    search() {
      return false;
    },
    width() {
      return "small";
    }
  },
  methods: {
    open() {
      this.$refs.dialog.open(this.value);
    },
    onSubmit(selected) {
      this.value = selected;
    }
  },
  template: `
    <div>
      <k-button @click="open">Open</k-button>

      <k-headline class="mt-8 mb-3">Value</k-headline>
      <k-code-block :code="value" />

      <k-files-dialog
        ref="dialog"
        :layout="layout"
        :max="max"
        :multiple="multiple"
        :search="search"
        :width="width"
        @submit="onSubmit"
      />
    </div>
  `
});

export const multiple = () => ({
  extends: single(),
  data() {
    return {
      value: ["4", "7"]
    };
  },
  computed: {
    multiple() {
      return true;
    }
  }
});

export const max = () => ({
  extends: multiple(),
  computed: {
    max() {
      return 3;
    }
  }
});

export const search = () => ({
  extends: single(),
  computed: {
    search() {
      return true;
    }
  }
});

export const layout = () => ({
  extends: single(),
  computed: {
    layout() {
      return "cardlets";
    },
    width() {
      return "large";
    }
  }
});
