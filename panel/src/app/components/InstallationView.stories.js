export default {
  title: "App | Views / Installation"
};

export const regular = () => ({
  template: `
    <k-installation-view />
  `
});

export const loading = () => ({
  template: `
    <k-installation-view :loading="true" />
  `
});

export const installing = () => ({
  template: `
    <k-installation-view :installing="true" />
  `,
});

export const translations = () => ({
  computed: {
    translations() {
      return [
        { value: "de", text: "Deutsch" },
        { value: "en", text: "English" },
      ];
    }
  },
  template: `
    <k-installation-view
      :translations="translations"
      translation="de"
    />
  `,
});


