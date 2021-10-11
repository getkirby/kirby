import Extension from "./Extension";

export default class Node extends Extension {
  constructor(options = {}) {
    super(options);
  }

  get type() {
    return "node";
  }

  get schema() {
    return null;
  }

  commands() {
    return {};
  }
}
