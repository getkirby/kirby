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
        content: this.onContent,
        input:   this.onInput
      };
    },
    lock() {
      return this.$store.state.content.locking.lock;
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
        return this.model.blueprint.tabs.map(tab => {
          tab.badge = this.$store.getters["content/badge"](this.storeId, tab);
          return tab;
        });
      }

      return  [];
    },
    unlocked() {
      return this.$store.state.content.locking.unlocked !== false;
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

      // create content store entry
      this.$store.dispatch("content/create", {
        id: this.storeId,
        values: this.model.content
      });

      // activate content locking if supported
      // and set initial state
      if (this.model.lock !== null) {
        this.$store.dispatch("content/hasLocking", true);
        this.$store.dispatch("content/lock", this.model.lock);
      }
    },
    async onContent(action) {
      switch (action) {
        case "download":
          return this.$store.dispatch("content/download");
        case "resolve":
          return this.$store.dispatch("content/unlocked", false);
        case "revert":
          return this.$store.dispatch("content/revert", this.storeId);
        case "save":
          return this.onSave();
        case "unlock":
          await this.$api.patch(
            this.$store.getters["content/api"](this.storeId) + "/unlock",
            null,
            null,
            true
          );
          return this.$store.dispatch("content/lock", false);
      }
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
