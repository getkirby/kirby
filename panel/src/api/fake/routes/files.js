import findFile from "../helpers/findFile.js";
import ok from "../helpers/ok.js";
import requestValues from "../helpers/requestValues.js";

export default (api) => {

  // files
  api.get("/:parentType/:parentId/files/:fileId", function (schema, request) {
    return findFile(schema, request);
  });

  api.patch("/:parentType/:parentId/files/:fileId", function (schema, request) {
    return findFile(schema, request).update({
      content: requestValues(request)
    })
  });

  api.post("/:parentType/:parentId/files", function (schema, request) {

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

  api.post("/:parentType/:parentId/files/search", function (schema, request) {
    return schema.files.where({ parentId: request.params.parentId });
  });

  // change filename
  api.patch("/:parentType/:parentId/files/:fileId/name", function (schema, request) {
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

  api.delete("/:parentType/:parentId/files/:fileId", function (schema, request) {
    findFile(schema, request).destroy();
    return ok();
  });

};

