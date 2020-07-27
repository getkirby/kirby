<script>
export default {
  props: {
    blueprint: String,
    next: Object,
    prev: Object,
    permissions: {
      type: Object,
      default() {
        return {}
      }
    },
    tab: {
      type: Object,
      default() {
        return {}
      }
    },
    tabs: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  computed: {
    isLocked() {
      return this.$store.state.content.status.lock !== null;
    }
  },
  created() {
    this.$events.$on("model.reload", this.$reload);
    this.$events.$on("keydown.left", this.toPrev);
    this.$events.$on("keydown.right", this.toNext);
  },
  destroyed() {
    this.$events.$off("model.reload", this.$reload);
    this.$events.$off("keydown.left", this.toPrev);
    this.$events.$off("keydown.right", this.toNext);
  },
  methods: {
    toPrev(e) {
      if (this.prev && e.target.localName === "body") {
        this.$go(this.prev.link);
      }
    },
    toNext(e) {
      if (this.next && e.target.localName === "body") {
        this.$go(this.next.link);
      }
    }
  }
};
</script>
