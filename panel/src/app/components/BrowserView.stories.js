import BrowserView from "./BrowserView.vue";

export default {
  title: "App | Views / Browser ",
  component: BrowserView
};

export const regular = () => ({
  components: {
    "k-browser-view": BrowserView
  },
  template: `
    <k-browser-view />
  `
});

export const noFetch = () => ({
  extends: regular(),
  template: `
    <k-browser-view :fetch="false" />
  `
});

export const noGrid = () => ({
  extends: regular(),
  template: `
    <k-browser-view :grid="false" />
  `
});

export const noFetchNoGrid = () => ({
  extends: regular(),
  template: `
    <k-browser-view
      :fetch="false"
      :grid="false"
    />
  `
});

