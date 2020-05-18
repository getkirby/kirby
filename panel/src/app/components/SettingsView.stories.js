import SettingsView from "./SettingsView.vue";

export default {
  title: "App | Views / Settings"
};

export const regular = () => ({
  components: {
    "k-settings-view": SettingsView
  },
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
  data() {
    return {
      languages: this.$model.languages.list(),
    }
  },
  template: `
    <k-settings-view
      :languages="languages"
      :multilang="true"
      license="K3-1234-5678"
      version="3.4"
    />
  `
});


