<script>
export default {
  props: {
    blueprint: String,
    next: Object,
    prev: Object,
    permissions: {
      type: Object,
      default() {
        return {};
      }
    },
    lock: {
      type: [Boolean, Object]
    },
    model: {
      type: Object,
      default() {
        return {};
      }
    },
    tab: {
      type: Object,
      default() {
        return {
          columns: []
        };
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
      return this.model.link;
    },
    isLocked() {
      return this.lock?.state === "lock";
    },
    protectedFields() {
      return [];
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
        api: this.id,
        content: this.model.content,
        ignore: this.protectedFields
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
