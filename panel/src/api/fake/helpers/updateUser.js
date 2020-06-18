import requestValues from "./requestValues.js";

export default (schema, request) => {
  return schema.users
    .find(request.params.id)
    .update(requestValues(request));
};
