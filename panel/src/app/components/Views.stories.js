import InstallationView from "./InstallationView.vue";
import InstallationIssuesView from "./InstallationIssuesView.vue";
import LoginView from "./LoginView.vue";
import { action } from "@storybook/addon-actions";

export default {
  title: "App | Views"
};

export const Installation = () => ({
  components: {
    "k-story-view": InstallationView
  },
  template: `
    <k-story-view />
  `
});

export const InstallationIssues = () => ({
  extends: Installation(),
  components: {
    "k-story-view": InstallationIssuesView
  },
});

export const Login = () => ({
  extends: Installation(),
  components: {
    "k-story-view": LoginView
  },
});


