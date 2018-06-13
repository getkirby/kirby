import api from "./api.js";

export default (parent, name, query) => {
  return api.get(parent + "/" + name, query);
};
