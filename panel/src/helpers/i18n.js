import str from "./string.js";

export class TranslationString extends String {

  constructor(key, data) {
    super(key)
    this.data = data;
  }

  toString() {
    const key    = this.valueOf();
    const string = window.panel.$translation.data[key];
    return string ? str.template(string, this.data) : string;
  }
}