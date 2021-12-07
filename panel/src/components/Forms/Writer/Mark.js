import Extension from "./Extension.js";

export default class Mark extends Extension {
  constructor(options = {}) {
    super(options);
  }

  command() {
    return () => {};
  }

  remove() {
    this.editor.removeMark(this.name);
  }

  get schema() {
    return null;
  }

  get type() {
    return "mark";
  }

  toggle() {
    return this.editor.toggleMark(this.name);
  }

  update(attrs) {
    this.editor.updateMark(this.name, attrs);
  }
}
