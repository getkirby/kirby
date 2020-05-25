import { Server, Model, belongsTo, hasMany } from "miragejs";

/* Fixtures */
import blueprints from "./fixtures/blueprints.js";
import files from "./fixtures/files.js";
import languages from "./fixtures/languages.js";
import pages from "./fixtures/pages.js";
import roles from "./fixtures/roles.js";
import sites from "./fixtures/sites.js";
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

// TODO: Don't rely on storybook mock data
import {
  File,
  Files,
  Page,
  Pages,
  User,
  Users
} from "../../../storybook/data/PickerItems.js";

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

    this.post("/auth/login", function(schema, request) {
      const params = JSON.parse(request.requestBody);
      const user = this.serialize(
        schema.users.findBy({
          email: params.email,
          password: params.password,
        })
      );

      if (!user) {
        return {
          status: "error",
          code: 400,
          message: "Invalid email or password",
        };
      }

      schema.sessions.create({
        user: user.data.id,
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
        code: 200,
      };
    });

    this.get("/auth", function(schema) {
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

    // files
    this.get("/:parent/:pageId/files/:fileId", function(schema, request) {
      return schema.files.find(
        request.params.parent +
          "/" +
          request.params.pageId +
          "/" +
          request.params.fileId
      );
    });

    this.get("/site", (schema) => {
      return schema.sites.first();
    });

    this.get("/roles");
    this.get("/roles/:id");

    this.get("/translations");
    this.get("/translations/:id");

    this.get("/users");
    this.get("/users/:id");

    // temp for models fields, dialogs, pickers
    // TODO: figure out actual endpoint
    const toItems = (request, model) => {
      return JSON.parse(request.queryParams.ids).map(id => model(id));
    }
    const toOptions = (request, models) => {
      return models(
        parseInt(request.queryParams.page),
        parseInt(request.queryParams.limit),
        request.queryParams.parent,
        request.queryParams.search
      );
    }
    this.get("/field/files/items", (schema, request) => {
      return toItems(request, File);
    });
    this.get("/field/files/options", (schema, request) => {
      return toOptions(request, Files);
    });
    this.get("/field/pages/items", (schema, request) => {
      return toItems(request, Page);
    });
    this.get("/field/pages/options", (schema, request) => {
      return toOptions(request, Pages);
    });
    this.get("/field/users/items", (schema, request) => {
      return toItems(request, User);
    });
    this.get("/field/users/options", (schema, request) => {
      return toOptions(request, Users);
    });

    // temp test
    this.post("/upload", (schema) => {
      return {
        status: "ok",
        code: 200,
      };
    });


    // whitelist
    this.passthrough(
      "https://raw.githubusercontent.com/mledoze/countries/master/countries.json"
    );
    this.passthrough("https://api.themoviedb.org/**");
  },
});
