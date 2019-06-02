export default {
  props: {
    blueprint: String,
    help: String,
    name: String,
    parent: String
  },
  methods: {
    load() {
      return this.$api.get(this.parent + '/sections/' + this.name);
    }
  }
};
