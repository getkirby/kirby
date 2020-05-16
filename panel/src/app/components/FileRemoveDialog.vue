<script>
import AsyncRemoveDialog from "@/ui/components/AsyncRemoveDialog.vue";

export default {
  extends: AsyncRemoveDialog,
  methods: {
    async load(parent, filename) {
      this.filename = filename;
      this.parent   = parent;
      this.file     = await this.$api.files.get(parent, filename);
      this.text     = this.$t('file.delete.confirm', {
        filename: filename
      });
    },
    async submit() {
      await this.$model.files.delete(
        this.file.id,
        this.parent,
        this.filename
      );
      return this.file;
    }
  }
}
</script>
