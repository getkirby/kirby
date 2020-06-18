import updatePage from "../helpers/updatePage.js";

export default (api) => {

  api.get("pages/:id", function (schema, request) {
    let page = schema.pages.find(request.params.id);
    let json = this.serialize(page);
    return json;
  });

  api.post("pages/:id/duplicate", (schema, request) => {
    throw "Not yet implemented";
  });

  api.get("pages/:id/children/blueprints", function (schema, request) {
    let blueprints = schema.blueprints.where({ name: "album" });
    let json = this.serialize(blueprints);

    return json.data.map(blueprint => {
      return {
        name: blueprint.name,
        title: blueprint.title
      }
    });
  });

  api.post("pages/:id/children/search", (schema, request) => {
    return schema.pages.where({ parentId: request.params.id });
  });

  api.patch("pages/:id", (schema, request) => {
    return schema.pages
      .find(request.params.id)
      .update({ content: requestValues(request) });
  });

  api.patch("pages/:id/slug", (schema, request) => {
    throw "Not implemented yet";
  });

  api.patch("pages/:id/status", (schema, request) => {
    return updatePage(schema, request);
  });

  api.patch("pages/:id/template", (schema, request) => {
    return updatePage(schema, request);
  });

  api.patch("pages/:id/title", (schema, request) => {
    return updatePage(schema, request);
  });

  api.delete("pages/:id", (schema, request) => {
    schema.pages.find(request.params.id).destroy();
    return ok();
  });

};

