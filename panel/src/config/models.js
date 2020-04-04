import Vue from "vue";

import files from "@/models/files.js";
import pages from "@/models/pages.js";
import roles from "@/models/roles.js";
import site from "@/models/site.js";
import translations from "@/models/translations.js";
import users from "@/models/users.js";

Vue.prototype.$model = {
  files: files,
  pages: pages,
  roles: roles,
  site: site,
  translations: translations,
  users: users
};
