import ok from "../helpers/ok.js";
import requestValues from "../helpers/requestValues.js";

export default (api) => {

  // authentication
  api.get("/auth", function (schema) {
    const session = schema.sessions.first();

    if (session) {
      const user = schema.users.find(session.attrs.user);

      if (user) {
        return this.serialize(user);
      }
    }
  });

  api.post("/auth/login", function (schema, request) {
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

  api.post("/auth/logout", (schema) => {
    let sessions = schema.sessions.all();
    sessions.destroy();
    return ok();
  });

};

