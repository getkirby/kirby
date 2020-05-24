export default {
  title: "App | Views / Browser "
};

export const regular = () => ({
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
