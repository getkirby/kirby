import requestValues from "../helpers/requestValues.js";

export default (api) => {

  api.get("system", (schema, request) => {
    const system = schema.systems.first();

    return {
      code: 200,
      data: system.attrs,
      status: "ok",
      type: "model"
    };
  });

  api.post("/system/install", (schema, request) => {
    let values = requestValues(request);

    values.roleId = values.role;
    delete values.role;

    const user = schema.users.create(values);

    return {
      code: 200,
      status: "ok",
      token: "test",
      user: user
    };
  });

  api.post("/system/register", (schema, request) => {
    const values = requestValues(request);

    if (values.license === "K3-test") {
      return ok();
    }

    throw "Invalid license key";
  });

};
