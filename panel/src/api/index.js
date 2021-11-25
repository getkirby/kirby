import auth from "./auth.js";
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
    endpoint: "/api",
    methodOverwrite: true,
    onPrepare(options) {
      return options;
    },
    onStart() {},
    onComplete() {},
    onSuccess() {},
    onParserError() {},
    onError(error) {
      window.console.log(error.message);
      throw error;
    }
  };

  const config = {
    ...defaults,
    ...(extensions.config || {})
  };

  let api = {
    ...config,
    ...request(config),
    ...extensions
  };

  api.auth = auth(api);
  api.files = files(api);
  api.languages = languages(api);
  api.pages = pages(api);
  api.roles = roles(api);
  api.system = system(api);
  api.site = site(api);
  api.translations = translations(api);
  api.users = users(api);

  /**
   * @deprecated 3.5.0
   * @todo remove in 3.7.0
   */
  api.files.rename = api.files.changeName;
  api.pages.slug = api.pages.changeSlug;
  api.pages.status = api.pages.changeStatus;
  api.pages.template = api.pages.changeTemplate;
  api.pages.title = api.pages.changeTitle;
  api.site.title = api.site.changeTitle;
  api.system.info = api.system.get;

  return api;
};
