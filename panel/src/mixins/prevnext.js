export default {
  created() {
    this.fetch();
    this.$events.$on("key.arrowLeft", this.toPrev);
    this.$events.$on("key.arrowRight", this.toNext);
  },
  destroyed() {
    this.$events.$off("key.arrowLeft", this.toPrev);
    this.$events.$off("key.arrowRight", this.toNext);
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
