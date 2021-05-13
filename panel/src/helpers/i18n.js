import str from "./string.js";

export class TranslationString extends String {
  static create(key, data = {}) {
    if (typeof key !== 'string') {
      return;
    }

    return new this(JSON.stringify({key, data}));
  }
  toString() {
    const value  = JSON.parse(this.valueOf());
    const string = window.panel.$translation.data[value.key];
    return string ? str.template(string, value.data) : string;
  }
}