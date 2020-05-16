import files from "@/models/files.js";
import pages from "@/models/pages.js";
import roles from "@/models/roles.js";
import site from "@/models/site.js";
import translations from "@/models/translations.js";
import users from "@/models/users.js";

export default {
  install(Vue, Api, Store) {
    const plugins = {
      $api: Api,
      $events: Vue.prototype.$events,
      $store: Store,
      $user: Vue.prototype.$user
    };

    Vue.prototype.$model = {
      files: files(Vue, plugins),
      pages: pages(Vue, plugins),
      roles: roles(Vue, plugins),
      site: site(Vue, plugins),
      translations: translations(Vue, plugins),
      users: users(Vue, plugins)
    };
  }
};
