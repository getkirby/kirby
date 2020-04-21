import Form from "./Form.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Foundation / Form",
  component: Form,
  decorators: [Padding]
};

export const simple = () => ({
  data() {
    return {
      values: {}
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: "Name",
          type: "text",
          width: "1/2"
        },
        email: {
          label: "Email",
          type: "email",
          width: "1/2"
        },
        text: {
          label: "Text",
          type: "textarea"
        }
      };
    }
  },
  methods: {
    input: action("input"),
    focus: action("focus"),
    submit: action("submit")
  },
  template: `
    <div>
      <k-form
        :fields="fields"
        v-model="values"
        class="mb-8"
        @focus="focus"
        @input="input"
        @submit="submit"
      />

      <k-headline class="mb-3">Values</k-headline>
      <k-code-block :code="values" />
    </div>
  `,
});

export const login = () => ({
  data() {
    return {
      credentials: {
        email: null,
        password: null,
      },
      remember: false,
      error: null,
    };
  },
  computed: {
    fields() {
      return {
        email: {
          label: "Email",
          type: "email",
        },
        password: {
          label: "Password",
          type: "password",
          counter: false,
        }
      }
    }
  },
  methods: {
    onLogin(values) {
      action("login")(values);
      this.error = "Something went wrong";
      this.$refs.form.focus();
    },
    onResetError() {
      this.error = null;
    }
  },
  template: `
    <k-view align="center">
      <k-form
        ref="form"
        :fields="fields"
        v-model="credentials"
        @submit="onLogin"
      >
        <k-notification
          slot="header"
          :message="error"
          type="error"
          class="mb-8 rounded-xs shadow-lg"
          @close="onResetError"
        />
        <footer
          slot="footer"
          class="pt-8 flex justify-between"
        >
          <k-toggle-input
            class="text-sm"
            text="Remember me"
            v-model="remember"
          />
          <k-button
            icon="check"
            type="submit"
            theme="positive">
            Login
          </k-button>
        </footer>
      </k-form>
    </k-view>
  `,
});
