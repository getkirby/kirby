import { Serializer } from "miragejs";
export default Serializer.extend({
  keyForModel() {
    return "data";
  },
  keyForCollection() {
    return "data";
  },
  serialize(object, request) {
    let json = Serializer.prototype.serialize.apply(this, arguments);

    // Add metadata, sort parts of the response, etc.
    json.status = "ok";
    json.code = 200;

    if (Array.isArray(json.data)) {
      json.pagination = {
        page: 1,
        total: json.data.length
      };
      json.type = "collection";
    } else {
      json.type = "model";
    }

    return json;
  },
});
