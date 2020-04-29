import BrowserView from "./BrowserView.vue";

export default {
  title: "UI | Views / BrowserView ",
  component: BrowserView
};

export const regular = () => ({
  template: `
    <k-browser-view />
  `
});

export const noFetch = () => ({
  template: `
    <k-browser-view :fetch="false" />
  `
});

export const noGrid = () => ({
  template: `
    <k-browser-view :grid="false" />
  `
});

export const noFetchNoGrid = () => ({
  template: `
    <k-browser-view
      :fetch="false"
      :grid="false"
    />
  `
});

