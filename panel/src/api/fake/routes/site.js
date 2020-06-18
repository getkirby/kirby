import requestValues from "../helpers/requestValues.js";

export default (api) => {

  api.get("/site", (schema) => {
    return schema.sites.first();
  });

  api.patch("/site/title", (schema, request) => {
    return schema.sites.first().update(requestValues(request));
  });

};
