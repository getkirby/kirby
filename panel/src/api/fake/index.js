import api from "../index.js";
import auth from "./auth.js";
import blueprints from "./blueprints.js";
import languages from "./languages.js";
import pages from "./pages.js";
import roles from "./roles.js";
import site from "./site.js";
import users from "./users.js";

const modules = [
  auth,
  blueprints,
  languages,
  pages,
  roles,
  site,
  users
];

let responses = {};
const requestMethods = [
  "delete",
  "get",
  "patch",
  "post"
];

modules.forEach(mod => {
  requestMethods.forEach(requestMethod => {
    responses[requestMethod] = {
      ...responses[requestMethod] || {},
      ...mod[requestMethod] || {}
    };
  });
});

const request = (responses) => {
  return (path, data) => {
    if (responses[path]) {
      return responses[path](data);
    }

    return {
      code: 500,
      status: "error",
      message: "Invalid API method"
    };
  };
};

export default api({
  delete: request(responses.delete),
  get: request(responses.get),
  patch: request(responses.patch),
  post: request(responses.post)
});
