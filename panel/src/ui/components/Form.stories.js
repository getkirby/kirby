import Form from "./Form.vue";
import Padding from "../storybook/Padding.js";
import { action } from "@storybook/addon-actions";

export default {
  title: "UI | Form / Form",
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
    autofocus() {
      return false;
    },
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
        :autofocus="autofocus"
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

export const autofocus = () => ({
  extends: simple(),
  computed: {
    autofocus() {
      return true;
    },
  }
});

export const complex = () => ({
  data() {
    return {
      values: {}
    };
  },
  computed: {
    fields() {
      return {
        checkboxes: {
          label: "Checkboxes",
          options: [
            "Option A",
            "Option B",
            "Option C"
          ],
          width: "1/2"
        },
        radio: {
          label: "Radios",
          options: [
            "Option A",
            "Option B",
            "Option C"
          ],
          width: "1/2"
        },
        date: {
          label: "Date",
          before: "Published on",
          width: "1/2"
        },
        range: {
          label: "Range",
          before: "Budget",
          tooltip: {
            before: "$"
          },
          step: 100,
          max: 100000,
          width: "1/2"
        },
        email: {
          label: "Email",
          width: "1/2"
        },
        url: {
          label: "URL",
          width: "1/2"
        },
        tel: {
          label: "Phone",
          before: "+49",
          width: "1/2"
        },
        number: {
          label: "Number",
          before: "Show",
          after: "projects per page",
          width: "1/2"
        },
        textarea: {
          label: "Textarea",
        },
        headline: {
          label: "I'm a headline, use me to provide some structure",
        },
        info: {
          label: "Info",
          text: "Info fields are great to provide help for editors",
        },
        toggle: {
          label: "Toggle",
          width: "1/2"
        },
        select: {
          label: "Select",
          width: "1/2",
          options: [
            "Design",
            "Photography",
            "Interaction",
            "Interior",
            "Architecture"
          ]
        },
        multiselect: {
          label: "Multiselect",
          options: [
            "Design",
            "Photography",
            "Interaction",
            "Interior",
            "Architecture"
          ],
          help: "Select multiple categories",
          width: "1/2"
        },
        tags: {
          label: "Tags",
          options: [
            "Design",
            "Photography",
            "Interaction",
            "Interior",
            "Architecture"
          ],
          width: "1/2"
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
          class="mb-8 rounded-xs shadow-md"
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
