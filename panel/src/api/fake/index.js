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

    // helpers
    const findFile = (schema, request) => {
      return schema.files.find(
        request.params.parentType + "/" +
        request.params.parentId + "/" +
        request.params.fileId
      );
    };

    const ok = (data = {}) => {
      return {
        status: "ok",
        code: 200,
        data: data
      }
    };

    const requestValues = (request) => {
      return JSON.parse(request.requestBody);
    };

    // temp for models fields, dialogs, pickers
    // TODO: figure out actual endpoint
    const toItems = (request, model) => {
      return JSON.parse(request.queryParams.ids).map(id => model(id));
    };

    const toOptions = (request, models) => {
      return models(
        parseInt(request.queryParams.page),
        parseInt(request.queryParams.limit),
        request.queryParams.parent,
        request.queryParams.search
      );
    };

    const updateUser = (schema, request) => {
      return schema.users
        .find(request.params.id)
        .update(requestValues(request));
    };

    // authentication
    this.get("/auth", function(schema) {
      const session = schema.sessions.first();

      if (session) {
        const user = schema.users.find(session.attrs.user);

        if (user) {
          return this.serialize(user);
        }
      }
    });

    this.post("/auth/login", function(schema, request) {
      const values = requestValues(request);
      const user = this.serialize(
        schema.users.findBy({
          email: values.email,
          password: values.password,
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
      return ok();
    });

    // blueprints
    this.get("/blueprints");
    this.get("/blueprints/:id");

    // fields
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

    // files
    this.get("/:parentType/:parentId/files/:fileId", function(schema, request) {
      return findFile(schema, request);
    });

    this.post("/:parentType/:parentId/files", function(schema, request) {

      const file = request.requestBody.get("file");

      return schema.files.create({
        id: request.params.parentType + "/" + request.params.parentId + "/" + file.name,
        filename: file.name,
        extension: file.name.split(".").pop(),
        name: file.name.split(".").slice(0, -1).join('.'),
        parentId: request.params.parentId,
        size: file.size,
        niceSize: file.size + "kb",
        mime: file.type,
        template: "image",
        url: "https://source.unsplash.com/user/erondu/1600x900"
      });
    });

    this.post("/:parentType/:parentId/files/search", function(schema, request) {
      return schema.files.where({ parentId: request.params.parentId });
    });

    this.patch("/:parentType/:parentId/files/:fileId/name", function(schema, request) {
      let oldFile = findFile(schema, request);
      const values = requestValues(request);
      const filename = values.name + "." + oldFile.extension;
      const newFile = schema.files.create({
        ...oldFile.attrs,
        id: request.params.parentType + "/" + request.params.parentId + "/" + filename,
        name: values.name,
        filename: filename
      });

      oldFile.destroy();
      return newFile;
    });

    this.delete("/:parentType/:parentId/files/:fileId", function(schema, request) {
      findFile(schema, request).destroy();
      return ok();
    });

    // languages
    this.resource("languages");

    this.post("languages", (schema, request) => {
      const values = requestValues(request);
      return schema.languages.create({
        ...values,
        code: values.code.toLowerCase()
      });
    });

    this.get("/languages/:code", function (schema, request) {
      return schema.languages.findBy({ code: request.params.code });
    });

    this.patch("/languages/:code", (schema, request) => {
      return schema.languages
          .find(request.params.code)
          .update(requestValues(request));
    });

    this.delete("/languages/:code", (schema, request) => {
      schema.languages.find(request.params.code).destroy();
      return ok();
    });

    // pages
    this.resource("pages");
    this.post("pages/:id/children/search", (schema, request) => {
      return schema.pages.where({ parentId: request.params.id });
    });

    // roles
    this.get("/roles");
    this.get("/roles/:id");

    // site
    this.get("/site", (schema) => {
      return schema.sites.first();
    });

    this.patch("/site/title", (schema, request) => {
      return schema.sites.first().update(requestValues(request));
    });

    this.post("/system/register", (schema, request) => {
      const values = requestValues(request);

      if (values.license === "K3-test") {
        return ok();
      }

      throw "Invalid license key";
    });

    // translations
    this.get("/translations");
    this.get("/translations/:id");

    this.get("/users");
    this.post("/users", (schema, request) => {
      return schema.users.create(requestValues(request));
    });
    this.get("/users/:id");
    this.patch("/users/:id", (schema, request) => {
      return updateUser(schema, request);
    });
    this.patch("/users/:id/email", (schema, request) => {
      return updateUser(schema, request);
    });
    this.patch("/users/:id/language", (schema, request) => {
      return updateUser(schema, request);
    });
    this.patch("/users/:id/name", (schema, request) => {
      return updateUser(schema, request);
    });
    this.patch("/users/:id/password", (schema, request) => {
      return updateUser(schema, request);
    });
    this.patch("/users/:id/role", (schema, request) => {
      return updateUser(schema, request);
    });



    this.delete("/users/:id", (schema, request) => {
      schema.users.find(request.params.id).destroy();
      return ok();
    });

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
