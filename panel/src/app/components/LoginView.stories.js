import LoginView from "./LoginView.vue";

export default {
  title: "App | Views / Login",
  component: LoginView
};

export const regular = () => ({
  template: `
    <k-login-view />
  `
});

