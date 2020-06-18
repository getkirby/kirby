import { Server, Model, belongsTo, hasMany } from "miragejs";

/* Fixtures */
import blueprints from "./fixtures/blueprints.js";
import files from "./fixtures/files.js";
import languages from "./fixtures/languages.js";
import pages from "./fixtures/pages.js";
import roles from "./fixtures/roles.js";
import sites from "./fixtures/sites.js";
import systems from "./fixtures/systems.js";
import translations from "./fixtures/translations.js";
import users from "./fixtures/users.js";

/* Serializers */
import blueprintSerializer from "./serializers/blueprint.js";
import fileSerializer from "./serializers/file.js";
import languageSerializer from "./serializers/language.js";
import pageSerializer from "./serializers/page.js";
import roleSerializer from "./serializers/role.js";
import siteSerializer from "./serializers/site.js";
import translationSerializer from "./serializers/translation.js";
import userSerializer from "./serializers/user.js";

/** Helpers */
import ok from "./helpers/ok.js";

/** Routes */
import authRoutes from "./routes/auth.js";
import fieldRoutes from "./routes/fields.js";
import fileRoutes from "./routes/files.js";
import languageRoutes from "./routes/languages.js";
import pageRoutes from "./routes/pages.js";
import roleRoutes from "./routes/roles.js";
import siteRoutes from "./routes/site.js";
import systemRoutes from "./routes/system.js";
import translationRoutes from "./routes/translations.js";
import userRoutes from "./routes/users.js";

/* API */
new Server({
  models: {
    blueprint: Model.extend(),
    file: Model.extend({
      parent: belongsTo("page"),
    }),
    language: Model,
    page: Model.extend({
      files: hasMany("file"),
      drafts: hasMany("page"),
      children: hasMany("page"),
    }),
    role: Model,
    session: Model,
    site: Model.extend({
      files: hasMany("file"),
      drafts: hasMany("page"),
      children: hasMany("page"),
    }),
    system: Model,
    translation: Model,
    user: Model.extend({
      role: belongsTo(),
    }),
  },
  fixtures: {
    blueprints: blueprints,
    files: files,
    languages: languages,
    pages: pages,
    roles: roles,
    sites: sites,
    systems: systems,
    translations: translations,
    users: users,
  },
  serializers: {
    blueprint: blueprintSerializer,
    file: fileSerializer,
    language: languageSerializer,
    page: pageSerializer,
    role: roleSerializer,
    site: siteSerializer,
    translation: translationSerializer,
    user: userSerializer,
  },
  seeds(server) {
    server.loadFixtures();
  },
  routes() {
    this.namespace = "api";

    authRoutes(this);
    fieldRoutes(this);
    fileRoutes(this);
    languageRoutes(this);
    pageRoutes(this);
    roleRoutes(this);
    siteRoutes(this);
    systemRoutes(this);
    translationRoutes(this);
    userRoutes(this);

    // temp test
    this.post("/upload", (schema) => {
      return ok();
    });

    // whitelist
    this.passthrough(
      "https://raw.githubusercontent.com/mledoze/countries/master/countries.json"
    );

    this.passthrough("https://api.themoviedb.org/**");
  },
});
