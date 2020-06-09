<script>
export default {
  data() {
    return {
      model: null,
      saving: false
    };
  },
  computed: {
    changes() {
      return this.$store.getters["content/hasChanges"](this.storeId);
    },
    language() {
      return this.$store.state.languages.current;
    },
    listeners() {
      return {
        contentDownload: this.onContentDownload,
        contentResolve:  this.onContentResolve,
        contentRevert:   this.onContentRevert,
        contentSave:     this.save,
        contentUnlock:   this.onContentUnlock,
        input:           this.onInput,
      };
    },
    lock() {
      return this.$store.state.content.current.lock;
    },
    storeId() {
      return this.id;
    },
    tab() {
      const current = this.$route.hash.slice(1) || "main";
      const tab = this.tabs.find(tab => tab.name === current) || this.tabs[0];
      return tab;
    },
    tabs() {
      if (this.model.blueprint && this.model.blueprint.tabs) {
        return this.model.blueprint.tabs;
      }

      return  [];
    },
    unlocked() {
      return this.$store.state.content.current.unlocked !== false;
    },
    values() {
      return this.$store.getters["content/values"](this.storeId);
    },
    viewDefaults() {
      if (!this.model) {
        return {};
      }

      return {
        changes:  this.changes,
        columns:  this.tab.columns || {},
        lock:     this.lock,
        next:     this.model.next,
        prev:     this.model.prev,
        saving:   this.saving,
        tabs:     this.tabs,
        tab:      this.tab.name,
        unlocked: this.unlocked,
        value:    this.values
      };
    }
  },
  watch: {
    language() {
      this.reload();
    },
    lock(newValue, oldValue) {
      if (newValue === false && oldValue !== false) {
        this.reload();
      }
    }
  },
  methods: {
    load(model) {
      this.model = model;
      this.onTitle();

      this.$store.dispatch("content/create", {
        id: this.storeId,
        values: this.model.content
      });
    },
    onContentDownload() {
      this.$store.dispatch("content/download");
    },
    onContentResolve() {
      this.$store.dispatch("content/unlocked", false);
    },
    onContentRevert() {
      this.$store.dispatch("content/revert", this.storeId);
    },
    onContentUnlock() {
      this.$store.dispatch("content/unlock");
    },
    onInput(values) {
      this.$store.dispatch("content/input", {
        id: this.storeId,
        values: values
      });
    },
    async onSave() {
      this.saving = true;
      await this.save();
      this.saving = false;
    },
    onTitle() {
      this.$model.system.title(this.model.id);
    },
    async save() {
      // Hit the model API
      return {};
    }
  }
}
</script>
