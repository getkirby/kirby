import InstallationView from "./InstallationView.vue";

export default {
  title: "App | Views / Installation"
};

export const regular = () => ({
  components: {
    "k-installation-view": InstallationView
  },
  template: `
    <k-installation-view />
  `
});

