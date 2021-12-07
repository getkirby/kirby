export default {
  props: {
    id: {
      type: [Number, String],
      default() {
        return this._uid;
      }
    }
  }
};
