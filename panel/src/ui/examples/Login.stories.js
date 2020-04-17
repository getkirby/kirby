
export default {
  title: "Views / Login"
};

export const regular = () => ({
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
  template: `
    <k-view align="center">
      <form>
        <k-email-field v-bind="fields.email" class="mb-6" />
        <k-password-field v-bind="fields.password" class="mb-6" />
        <div class="flex justify-between">
          <k-toggle-input text="Remember me" class="text-sm" />
          <k-button icon="check" theme="positive">Login</k-button>
        </div>
      </form>
    </k-view>
  `,
});
