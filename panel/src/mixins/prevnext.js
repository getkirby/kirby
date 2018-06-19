export default {
  created() {
    this.fetch();
    this.$events.$on("keydown.left", this.toPrev);
    this.$events.$on("keydown.right", this.toNext);
  },
  destroyed() {
    this.$events.$off("keydown.left", this.toPrev);
    this.$events.$off("keydown.right", this.toNext);
  },
  watch: {
    $route() {
      this.fetch();
    }
  },
  methods: {
    toPrev(e) {
      if (this.prev && e.target.localName === "body") {
        this.$router.push(this.prev.link);
      }
    },
    toNext(e) {
      if (this.next && e.target.localName === "body") {
        this.$router.push(this.next.link);
      }
    }
  }
};
