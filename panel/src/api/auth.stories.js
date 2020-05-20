import Padding from "../../storybook/theme/Padding.js";

export default {
  title: "Internals | $api / auth",
  decorators: [Padding]
};

export const login = () => ({
  template: `
    <api-example
      call="this.$api.auth.login({ email: 'ada@getkirby.com', password: 'demodemo' })"
      method="POST"
      endpoint="/api/auth/login"
    />
  `
});

export const logout = () => ({
  template: `
    <api-example
      call="this.$api.auth.logout()"
      method="POST"
      endpoint="/api/auth/logout"
    />
  `
});

export const user = () => ({
  created() {
    this.$api.auth.login({ email: "ada@getkirby.com", password: "demodemo" });
  },
  template: `
    <api-example
      call="this.$api.auth.user()"
      method="GET"
      endpoint="/api/auth"
    />
  `
});

