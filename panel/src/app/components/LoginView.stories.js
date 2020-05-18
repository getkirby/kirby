import LoginView from "./LoginView.vue";

export default {
  title: "App | Views / Login"
};

export const regular = () => ({
  components: {
    "k-login-view": LoginView
  },
  template: `
    <k-login-view />
  `
});

