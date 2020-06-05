
export default (Vue) => {
  const api = async ({endpoint, query, limit, fields, map}) => {
    const response = await Vue.$api.get(endpoint, {
      q: query,
      limit: Vue.$config.search.limit || limit,
      select: ["id", ...fields, "panelPreview"]
    });

    return response.data.map(map);
  };

  return {
    pages: {
      label: "Pages",
      icon: "page",
      search: async (params) => api({
        ...params,
        endpoint: "site/search",
        fields: ["title"],
        map: page => ({
          id:      page.id,
          title:   page.title,
          link:    Vue.$model.pages.link(page.id),
          info:    page.id,
          preview: page.panelPreview
        })
      })
    },
    files: {
      label: "Files",
      icon: "image",
      search: async (params) => api({
        ...params,
        endpoint: "files/search",
        fields: ["filename", "parent"],
        map: file => ({
          id:    file.id,
          title: file.filename,
          link:  Vue.$model.files.link(
            Vue.$model.pages.url(file.parent.id),
            file.filename
          ),
          info:    file.id,
          preview: file.panelPreview
        })
      })
    },
    users: {
      label: "Users",
      icon: "users",
      search: async (params) => api({
        ...params,
        endpoint: "users/search",
        fields: ["name", "email"],
        map: user => ({
          id:      user.id,
          title:   user.name || user.email,
          link:    Vue.$model.users.link(user.id),
          info:    user.email,
          preview: user.panelPreview
        })
      })
    }
  };
}
