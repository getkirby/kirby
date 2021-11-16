export default (api) => {
  return {
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
