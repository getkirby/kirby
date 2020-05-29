<script>
export default {
  props: {
    id: {
      type: String
    },
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
    storeId() {
      return this.id;
    },
    values() {
      return this.$store.getters["content/values"](this.storeId);
    },
    view() {
      return {};
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
      await this.$store.dispatch("content/hasUnlock", this.storeId);
      this.$store.dispatch("content/create", {
        id: this.storeId,
        values: this.model.content
      });
    },
    async loadModel() {
      return {};
    },
    onInput(values) {
      this.$store.dispatch("content/input", { id: this.storeId, values: values });
    },
    onRevert() {
      this.$store.dispatch("content/revert", this.storeId);
    },
    async onSave() {
      this.saving = true;
      const values = this.$store.getters["content/values"](this.storeId);
      await this.saveModel(values);
      this.$store.dispatch("content/update", { id: this.storeId, values: values });
      this.saving = false;
    },
    async saveModel(values) {
      // Hit the model API
      return {};
    }
  }
}
</script>
