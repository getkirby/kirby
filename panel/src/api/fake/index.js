import { Server, Model, belongsTo, hasMany } from "miragejs";

/* Fixtures */
import blueprints from "./fixtures/blueprints.js";
import files from "./fixtures/files.js";
import languages from "./fixtures/languages.js";
import pages from "./fixtures/pages.js";
import roles from "./fixtures/roles.js";
import sites from "./fixtures/sites.js";
import users from "./fixtures/users.js";

/* Serializers */
import blueprintSerializer from "./serializers/blueprint.js";
import fileSerializer from "./serializers/file.js";
import languageSerializer from "./serializers/language.js";
import pageSerializer from "./serializers/page.js";
import roleSerializer from "./serializers/role.js";
import siteSerializer from "./serializers/site.js";
import userSerializer from "./serializers/user.js";

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
    users: users,
  },
  serializers: {
    blueprint: blueprintSerializer,
    file: fileSerializer,
    language: languageSerializer,
    page: pageSerializer,
    role: roleSerializer,
    site: siteSerializer,
    user: userSerializer,
  },
  seeds(server) {
    server.loadFixtures();
  },
  routes() {
    this.namespace = "api";

    this.post("/auth/login", function (schema, request) {
      const params = JSON.parse(request.requestBody);
      const user  = this.serialize(schema.users.findBy({
        email: params.email,
        password: params.password
      }));

      if (!user) {
        return {
          status: "error",
          code: 400,
          message: "Invalid email or password",
        };
      }

      schema.sessions.create({
        user: user.data.id
      });

      return {
        status: "ok",
        code: 200,
        user: user.data,
      };
    });

    this.post("/auth/logout", (schema) => {
      let sessions = schema.sessions.all();
      sessions.destroy();
      return {
        status: "ok",
        code: 200
      };
    });

    this.get("/auth", function (schema) {
      const session = schema.sessions.first();

      if (session) {
        const user = schema.users.find(session.attrs.user);

        if (user) {
          return this.serialize(user);
        }
      }
    });

    this.get("/blueprints");
    this.get("/blueprints/:id");

    this.resource("languages");
    this.resource("pages");

    this.get("/site", (schema) => {
      return schema.sites.first();
    });

    this.get("/roles");
    this.get("/roles/:id");

    this.get("/users");
    this.get("/users/:id");
  },
});
