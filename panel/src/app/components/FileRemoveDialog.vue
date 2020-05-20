<script>
import AsyncRemoveDialog from "@/ui/components/AsyncRemoveDialog.vue";

export default {
  extends: AsyncRemoveDialog,
  methods: {
    async load(parent, filename) {
      const file = await this.$api.files.get(parent, filename);

      this.parent   = parent;
      this.id       = file.id;
      this.filename = file.filename;

      this.text = this.$t('file.delete.confirm', {
        filename: this.filename
      });
    },
    async submit() {
      return await this.$model.files.delete(
        this.id,
        this.parent,
        this.filename
      );
    }
  }
}
</script>
