export default (api) => {

  let pages = {
    id(id) {
      return id.replace(/\//g, "+");
    },
    async blueprints(parent, section) {
      return api.get("pages/" + this.id(parent) + "/children/blueprints", {
        section: section
      });
    },
    async create(parent, data) {
      if (parent === null || parent === "/") {
        return api.post("site/children", data);
      }

      return api.post("pages/" + this.id(parent) + "/children", data);
    },
    async duplicate(id, slug, options) {
      return api.post("pages/" + this.id(id) + "/duplicate", {
        slug:     slug,
        children: options.children || false,
        files:    options.files    || false,
      });
    },
    async get(id, query) {
      let page = await api.get("pages/" + this.id(id), query);

      if (Array.isArray(page.content) === true) {
        page.content = {};
      }

      return page;
    },
    async preview(id) {
      const page = await this.get(this.id(id), { select: "previewUrl" });
      return page.previewUrl;
    },
    async update(id, data) {
      return api.patch("pages/" + this.id(id), data);
    },
    async changeSlug(id, slug) {
      return api.patch("pages/" + this.id(id) + "/slug", { slug: slug });
    },
    async changeStatus(id, status, position) {
      return api.patch("pages/" + this.id(id) + "/status", {
        status: status,
        position: position
      });
    },
    async changeTemplate(id, template) {
      return api.patch("pages/" + this.id(id) + "/template", {
        template: template
      });
    },
    async changeTitle(id, title) {
      return api.patch("pages/" + this.id(id) + "/title", { title: title });
    },
    async children(id, query) {
      return api.post("pages/" + this.id(id) + "/children/search", query);
    },
    async files(id, query) {
      return api.post("pages/" + this.id(id) + "/files/search", query);
    },
    async delete(id, data) {
      return api.delete("pages/" + this.id(id), data);
    },
    async search(parent, query) {
      if (parent) {
        return api.post('pages/' + this.id(parent) + '/children/search?select=id,title,hasChildren', query);
      }

      return api.post('site/children/search?select=id,title,hasChildren', query);
    },
  };

  // deprecated aliases
  pages.slug     = pages.changeSlug;
  pages.status   = pages.changeStatus;
  pages.template = pages.changeTitle;
  pages.title    = pages.changeTemplate;

  return pages;

};
