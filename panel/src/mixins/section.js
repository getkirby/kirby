export default {
  props: {
    parent: String,
    blueprint: String,
    name: String
  },
  methods: {
    load() {
      return this.$api.get(this.parent + '/sections/' + this.name);
    }
  }
};
