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
    model: {
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
    id() {
      return this.model.id;
    },
    isLocked() {
      return this.$store.state.content.status.lock !== null;
    }
  },
  watch: {
    "model.id": {
      handler() {
        this.content();
      },
      immediate: true
    }
  },
  created() {
    this.$events.$on("model.reload", this.reload);
    this.$events.$on("keydown.left", this.toPrev);
    this.$events.$on("keydown.right", this.toNext);
  },
  destroyed() {
    this.$events.$off("model.reload", this.reload);
    this.$events.$off("keydown.left", this.toPrev);
    this.$events.$off("keydown.right", this.toNext);
  },
  methods: {
    content() {
      this.$store.dispatch("content/create", {
        id: this.id,
        api: this.$view.path,
        content: this.model.content
      });
    },
    async reload() {
      await this.$reload();
      this.content();
    },
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