const roles = {
  admin: {
    name: "admin",
    title: "Admin"
  },
  client: {
    name: "client",
    title: "Client"
  },
  editor: {
    name: "editor",
    title: "Editor"
  },
};

export default {
  get: {
    "roles": () => (roles),
    "roles/admin": () => (roles.admin),
    "roles/client": () => (roles.client),
    "roles/editor": () => (roles.editor)
  }
};
