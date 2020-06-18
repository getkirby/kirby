import requestValues from "./requestValues.js";

export default (schema, request) => {
  return schema.pages
    .find(request.params.id)
    .update(requestValues(request));
};
