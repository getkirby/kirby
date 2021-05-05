import Vue from "vue";

export default (api) => {
  return {
    breadcrumb(file, route) {

      let parent = null;
      let breadcrumb = [];

      switch (route) {
        case "UserFile":
          breadcrumb.push({
            label: file.parent.username,
            link: api.users.link(file.parent.id)
          });
          parent = 'users/' + file.parent.id;
          break;
        case "SiteFile":
          parent = "site";
          break;
        case "PageFile":
          breadcrumb = file.parents.map(parent => ({
            label: parent.title,
            link: api.pages.link(parent.id)
          }));
          parent = api.pages.url(file.parent.id);
          break;
      }

      breadcrumb.push({
        label: file.filename,
        link: this.link(parent, file.filename)
      });

      return breadcrumb;
    },
    async changeName(parent, filename, to) {
      return api.patch(parent + "/files/" + filename + "/name", {
        name: to
      });
    },
    async delete(parent, filename) {
      return api.delete(parent + "/files/" + filename);
    },
    async get(parent, filename, query) {
      let file = await api.get(parent + "/files/" + filename, query);

      if (Array.isArray(file.content) === true) {
        file.content = {};
      }

      return file;
    },
    link(parent, filename, path) {
      return "/" + this.url(parent, filename, path);
    },
    async options(parent, filename, view, sortable = true) {
      const file    = await api.get(this.url(parent, filename), {select: "options"});
      const options = file.options;
      let result    = [];

      if (view === "list") {
        result.push({
          click: "download",
          icon: "open",
          text: Vue.$t("open"),
        });

        result.push("-");
      }

      result.push({
        click: "rename",
        icon: "title",
        text: Vue.$t("rename"),
        disabled: !options.changeName
      });

      result.push({
        click: "replace",
        icon: "upload",
        text: Vue.$t("replace"),
        disabled: !options.replace
      });

      if (view === "list") {
        result.push("-");

        result.push({
          click: "sort",
          icon: "sort",
          text: Vue.$t("file.sort"),
          disabled: !(options.update  && sortable)
        });
      }

      result.push("-");

      result.push({
        click: "remove",
        icon: "trash",
        text: Vue.$t("delete"),
        disabled: !options.delete
      });

      return result;
    },
    async update(parent, filename, data) {
      return api.patch(parent + "/files/" + filename, data);
    },
    url(parent, filename, path) {
      let url = parent + "/files/" + filename;

      if (path) {
        url += "/" + path;
      }

      return url;
    }
  };
};
