import Padding from "../../../../storybook/theme/Padding.js";

export default {
  title: "Internals | $store / notification",
  decorators: [Padding]
};

export const state = () => ({
  data() {
    return {
      value: {}
    };
  },
  computed: {
    fields() {
      return {
        type: {
          type: "select",
          options: ["success", "error", "info"],
          width: "1/3"
        },
        message: {
          type: "text",
          width: "2/3"
        },
        permanent: {
          type: "toggle",
          width: "1/3"
        },
        details: {
          type: "text",
          width: "2/3"
        }
      };
    }
  },
  methods: {
    onSubmit() {
      this.$store.dispatch("notification/" + this.value.type, {
        ...this.value,
        details: this.value.details ? [this.value.details] : null
      });
    }
  },
  template: `
    <div>
      <k-fieldset
        v-model="value"
        :fields="fields"
        class="mb-4"
      />
      <k-button icon="bell" @click="onSubmit" >
        Send notification
      </k-button>

      <k-code-block :code="$store.state.notification" class="mt-8" />
      <k-notifications />
    </div>
  `
});
