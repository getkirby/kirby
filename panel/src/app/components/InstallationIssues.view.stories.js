export default {
  title: "App | Views / Installation Issues"
};

export const disabled = () => ({
  template: `
    <k-installation-issues-view :disabled="true" />
  `
});

export const accounts = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :accounts="false" />
  `
});

export const content = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :content="false" />
  `
});

export const curl = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :curl="false" />
  `
});

export const mbstring = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :mbstring="false" />
  `
});

export const media = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :media="false" />
  `
});

export const php = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :php="false" />
  `
});

export const server = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :server="false" />
  `
});

export const sessions = () => ({
  extends: disabled(),
  template: `
    <k-installation-issues-view :sessions="false" />
  `
});
