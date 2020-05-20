export default (api) => {

  let files = {
    async changeName(parent, filename, to) {
      return api.patch(parent + "/files/" + filename + "/name", {
        name: to
      });
    },
    async get(parent, filename, query) {
      let file = await api.get(parent + "/files/" + filename, query);

      if (Array.isArray(file.content) === true) {
        file.content = {};
      }

      return file;
    },
    async update(parent, filename, data) {
      return api.patch(parent + "/files/" + filename, data);
    },
    async delete(parent, filename) {
      return api.delete(parent + "/files/" + filename);
    }
  };

  // deprecated aliases
  files.rename = files.changeName;

  return files;
};
