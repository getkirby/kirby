export default {
  title: "App | Views / Login"
};

export const regular = () => ({
  template: `
    <k-login-view />
  `
});

export const loading = () => ({
  template: `
    <k-login-view :loading="true" />
  `,
});

export const processing = () => ({
  template: `
    <k-login-view :processing="true" />
  `,
});
