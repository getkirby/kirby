export default class Extension {
  constructor(options = {}) {
    this.options = {
      ...this.defaults,
      ...options
    };
  }

  init() {
    return null;
  }

  bindEditor(editor = null) {
    this.editor = editor;
  }

  get name() {
    return null;
  }

  get type() {
    return "extension";
  }

  get defaults() {
    return {};
  }

  plugins() {
    return [];
  }

  inputRules() {
    return [];
  }

  pasteRules() {
    return [];
  }

  keys() {
    return {};
  }
}
