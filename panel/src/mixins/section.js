export default {
  props: {
    parent: String,
    blueprint: String,
    name: String
  },
  watch: {
    blueprint() {
      this.fetch();
    }
  }
};
