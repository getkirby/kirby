export default {
  title: "App | Routes / Users"
};

export const regular = () => ({
  template: `
    <k-users-route />
  `
});

export const editorRole = () => ({
  template: `
    <k-users-route role="editor" />
  `,
});

export const clientRole = () => ({
  template: `
    <k-users-route role="client" />
  `,
});
