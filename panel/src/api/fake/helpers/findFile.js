export default (schema, request) => {
  return schema.files.find(
    request.params.parentType + "/" +
    request.params.parentId + "/" +
    request.params.fileId
  );
};
