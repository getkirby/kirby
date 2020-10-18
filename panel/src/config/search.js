
export default (app) => {
  const api = async ({endpoint, query, limit, fields, map}) => {
    const response = await app.$api.get(endpoint, {
      q: query,
      limit: limit || 10,
      select: ["id", ...fields, "panelIcon", "panelImage"]
    });

    return response.data.map(item => {
      return {
        id:    item.id,
        icon:  {...item.panelIcon, back: "black", color: "#fff"},
        image: {...item.panelImage, back: "pattern", cover: true},
        ...map(item)
      }
    });
  };

  return {
    pages: {
      label: app.$t("pages"),
      icon: "page",
      search: async (params) => api({
        ...params,
        endpoint: "site/search",
        fields: ["title"],
        map: page => ({
          title:   page.title,
          link:    app.$api.pages.link(page.id),
          info:    page.id
        })
      })
    },
    files: {
      label: app.$t("files"),
      icon: "image",
      search: async (params) => api({
        ...params,
        endpoint: "files/search",
        fields: ["filename", "parent"],
        map: file => ({
          title: file.filename,
          link:  app.$api.files.link(
            app.$api.pages.url(file.parent.id),
            file.filename
          ),
          info:  file.id
        })
      })
    },
    users: {
      label: app.$t("users"),
      icon: "users",
      search: async (params) => api({
        ...params,
        endpoint: "users/search",
        fields: ["name", "email"],
        map: user => ({
          title: user.name || user.email,
          link:  app.$api.users.link(user.id),
          info:  user.email,
          icon:  {
            back: "black",
            type: "user"
          }
        })
      })
    }
  };
}
