import LoginRoute from "./LoginRoute.vue";

export default {
  title: "App | Routes / Login ",
  component: LoginRoute
};

export const regular = () => ({
  template: `
    <k-login-route />
  `
});
