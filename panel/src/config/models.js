import files from "@/models/files.js";
import pages from "@/models/pages.js";
import roles from "@/models/roles.js";
import site from "@/models/site.js";
import translations from "@/models/translations.js";
import users from "@/models/users.js";

export default {
  install(Vue) {

    Vue.prototype.$model = {
      files: files(Vue),
      pages: pages(Vue),
      roles: roles(Vue),
      site: site(Vue),
      translations: translations(Vue),
      users: users(Vue)
    };
  }
};
