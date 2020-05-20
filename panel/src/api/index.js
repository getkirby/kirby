import auth from "./auth.js";
import blueprints from "./blueprints.js";
import files from "./files.js";
import languages from "./languages.js";
import pages from "./pages.js";
import request from "./request.js";
import roles from "./roles.js";
import system from "./system.js";
import site from "./site.js";
import translations from "./translations.js";
import users from "./users.js";

export default (extensions = {}) => {

  const defaults = {
    onPrepare() { },
    onStart() { },
    onComplete() { },
    onSuccess() { },
    onError(error) {
      window.console.log(error.message);
      throw error;
    }
  };

  const config = {
    ...defaults,
    ...(extensions.config || {})
  };

  const api = {
    ...config,
    ...request(config),
    ...extensions,
  };

  return {
    ...api,
    auth: auth(api),
    blueprints: blueprints(api),
    files: files(api),
    languages: languages(api),
    pages: pages(api),
    roles: roles(api),
    system: system(api),
    site: site(api),
    translations: translations(api),
    users: users(api)
  };
};
