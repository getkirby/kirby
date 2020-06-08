<script>
export default {
  props: {
    tab: {
      type: String,
      default: "main"
    }
  },
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
    lock() {
      return this.$store.state.content.current.lock;
    },
    storeId() {
      return this.id;
    },
    tabs() {
      return this.model.blueprint && this.model.blueprint.tabs ? this.model.blueprint.tabs : [];
    },
    unlocked() {
      return this.$store.state.content.current.unlocked;
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
        columns:  this.columns(this.tabs, this.tab),
        lock:     this.lock,
        next:     this.model.next,
        prev:     this.model.prev,
        saving:   this.saving,
        tabs:     this.tabs,
        tab:      this.tab,
        unlocked: this.unlocked,
        value:    this.values
      };
    }
  },
  methods: {
    columns(tabs, currentTab) {
      const tab = tabs.find(tab => tab.name === currentTab) || tabs[0];

      if (tab && tab.columns) {
        return tab.columns;
      }

      return {};
    },
    load(model) {
      this.model = model;

      // check for unlock
      // TODO: fake API route needed to uncomment the following line
      // const unlock = await this.$api.get(this.id + "/unlock");
      const unlock = {};
      if (unlock.supported === true && unlock.unlocked === true ) {
        this.$store.dispatch("content/unlocked",
          this.$store.getters["content/changes"](this.storeId)
        );
      }

      this.$store.dispatch("content/create", {
        id: this.storeId,
        values: this.model.content
      });
    },
    onInput(values) {
      this.$store.dispatch("content/input", {
        id: this.storeId,
        values: values
      });
    },
    async onLanguage(language) {
      await this.$model.system.load(true);
      this.load();
    },
    onRevert() {
      this.$store.dispatch("content/revert", this.storeId);
    },
    async onSave() {
      this.saving = true;
      await this.save();
      this.saving = false;
    },
    async save() {
      // Hit the model API
      return {};
    }
  }
}
</script>
