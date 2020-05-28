import Padding from "../../../../storybook/theme/Padding.js";

export default {
  title: "Internals | $store / notification",
  decorators: [Padding]
};

export const state = () => ({
  computed: {
    actions() {
      return [
        "this.$store.dispatch('notification/success')",
        "this.$store.dispatch('notification/info')",
        "this.$store.dispatch('notification/error')"
      ]
    }
  },
  methods: {
    onAction(action) {
      eval(action);
    }
  },
  template: `
    <div>
      <k-headline class="mb-2">Actions</k-headline>
      <k-button-group>
        <k-button
          v-for="(action, index) in actions"
          icon="angle-right"
          class="mb-1 mr-1 bg-white font-mono text-xs"
          @click="onAction(action)"
        >
          {{ action }}
        </k-button>
      </k-button-group>

      <k-headline class="mt-8 mb-2">State</k-headline>
      <k-code-block :code="$store.state.notification" />
    </div>
  `
});
