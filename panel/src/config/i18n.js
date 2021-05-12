
/**
 * TranslationString prop type
 */
export class TranslationString extends String {
  toString() {
    const app   = window.panel.app;
    const value = this.valueOf();
    return app ? app.$t(value) : value;
  }
}