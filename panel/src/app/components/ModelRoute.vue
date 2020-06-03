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
      model: {},
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
    unlocked() {
      return this.$store.state.content.current.unlocked;
    },
    values() {
      return this.$store.getters["content/values"](this.storeId);
    },
    viewDefaults() {
      return {
        changes:  this.changes,
        columns:  this.columns(this.model.blueprint.tabs, this.tab),
        lock:     this.lock,
        saving:   this.saving,
        tabs:     this.model.blueprint.tabs,
        tab:      this.tab,
        unlocked: this.unlocked,
        value:    this.values
      };
    }
  },
  created() {
    this.load();
  },
  watch: {
    "$route": "load",
  },
  methods: {
    columns(tabs, currentTab) {
      const tab = tabs.find(tab => tab.name === currentTab) || tabs[0];
      return tab.columns || {};
    },
    async load() {
      this.model = await this.loadModel();

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
    async loadModel() {
      return {};
    },
    onInput(values) {
      this.$store.dispatch("content/input", {
        id: this.storeId,
        values: values
      });
    },
    onLanguage(language) {
      this.load();
    },
    onRevert() {
      this.$store.dispatch("content/revert", this.storeId);
    },
    async onSave() {
      this.saving = true;
      await this.saveModel();
      this.saving = false;
    },
    async saveModel() {
      // Hit the model API
      return {};
    }
  }
}
</script>
