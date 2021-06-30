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
    async options(parent, filename, view, overwrite = {}) {
      const url     = this.url(parent, filename);
      const file    = await api.get(url, {select: "options,url"});
      const options = file.options;
      let result    = [];

      const disabled = function (action) {
        return options[action] === false || overwrite[action] === false;
      };

      if (view === "list") {
        result.push({
          click() {
            window.open(file.url);
          },
          icon: "open",
          text: Vue.$t("open"),
        });

        result.push("-");
      }

      result.push({
        click() {
          this.$dialog(url + '/changeName');
        },
        icon: "title",
        text: Vue.$t("rename"),
        disabled: disabled("changeName")
      });

      result.push({
        click: "replace",
        icon: "upload",
        text: Vue.$t("replace"),
        disabled: disabled("replace")
      });

      if (view === "list") {
        result.push("-");

        result.push({
          click() {
            this.$dialog(url + '/changeSort');
          },
          icon: "sort",
          text: Vue.$t("file.sort"),
          disabled: disabled("update")
        });
      }

      result.push("-");

      result.push({
        click() {
          this.$dialog(url + '/delete');
        },
        icon: "trash",
        text: Vue.$t("delete"),
        disabled: disabled("delete")
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
