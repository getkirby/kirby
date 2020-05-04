import api from "./api.js";

export default {
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
  async rename(parent, filename, to) {
    return api.patch(parent + "/files/" + filename + "/name", {
      name: to
    });
  },
  async delete(parent, filename) {
    return api.delete(parent + "/files/" + filename);
  }
};
