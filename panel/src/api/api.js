import auth from "./auth.js";
import file from "./file.js";
import pages from "./pages.js";
import request from "./request.js";
import role from "./role.js";
import system from "./system.js";
import section from "./section.js";
import site from "./site.js";
import translation from "./translation.js";
import users from "./users.js";

export default {
  config: {
    onStart() {},
    onComplete() {},
    onSuccess() {},
    onError(error) {
      console.log(error.message);
      throw error;
    }
  },
  auth: auth,
  file: file,
  pages: pages,
  role: role,
  system: system,
  section: section,
  site: site,
  translation: translation,
  users: users,
  ...request
};
