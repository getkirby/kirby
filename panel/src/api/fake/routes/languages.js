import ok from "../helpers/ok.js";
import requestValues from "../helpers/requestValues.js";

export default (api) => {

  // languages
  api.resource("languages");

  api.post("languages", (schema, request) => {
    const values = requestValues(request);
    return schema.languages.create({
      ...values,
      code: values.code.toLowerCase()
    });
  });

  api.get("/languages/:code", function (schema, request) {
    return schema.languages.findBy({ code: request.params.code });
  });

  api.patch("/languages/:code", (schema, request) => {
    return schema.languages
      .findBy({ code: request.params.code })
      .update(requestValues(request));
  });

  api.delete("/languages/:code", (schema, request) => {
    schema.languages.findBy({ code: request.params.code }).destroy();
    return ok();
  });

};
