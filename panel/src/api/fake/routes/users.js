import ok from "../helpers/ok.js";
import requestValues from "../helpers/requestValues.js";
import updateUser from "../helpers/updateUser.js";

export default (api) => {

  api.get("/users");

  api.post("/users", (schema, request) => {
    let values = requestValues(request);

    values.roleId = values.role;
    delete values.role;

    return schema.users.create(values);
  });

  api.get("/users/:id");

  api.patch("/users/:id", (schema, request) => {
    return updateUser(schema, request);
  });

  api.patch("/users/:id/email", (schema, request) => {
    return updateUser(schema, request);
  });

  api.patch("/users/:id/language", (schema, request) => {
    return updateUser(schema, request);
  });

  api.patch("/users/:id/name", (schema, request) => {
    return updateUser(schema, request);
  });

  api.patch("/users/:id/password", (schema, request) => {
    return updateUser(schema, request);
  });

  api.patch("/users/:id/role", (schema, request) => {
    return updateUser(schema, request);
  });

  api.delete("/users/:id", (schema, request) => {
    schema.users.find(request.params.id).destroy();
    return ok();
  });

};
