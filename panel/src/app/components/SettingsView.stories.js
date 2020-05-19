import SettingsView from "./SettingsView.vue";

export default {
  title: "App | Views / Settings",
  component: SettingsView
};

export const regular = () => ({
  methods: {
    onRegister() {
      alert("register");
    }
  },
  template: `
    <k-settings-view
      version="3.4"
      @register="onRegister"
    />
  `
});

export const registered = () => ({
  extends: regular(),
  template: `
    <k-settings-view
      license="K3-1234-5678"
      version="3.4"
    />
  `
});

export const languages = () => ({
  extends: regular(),
  template: `
    <k-settings-view
      :multilang="true"
      license="K3-1234-5678"
      version="3.4"
    />
  `
});


