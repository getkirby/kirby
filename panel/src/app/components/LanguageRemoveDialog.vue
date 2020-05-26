<script>
import AsyncRemoveDialog from "@/ui/components/AsyncRemoveDialog.vue";

export default {
  extends: AsyncRemoveDialog,
  methods: {
    async load(code) {
      this.code     = code;
      this.language = await this.$api.languages.get(code);
      this.text     = this.$t('language.delete.confirm', {
        name: this.language.name
      });
    },
    async submit() {
      await this.$model.languages.delete(this.code);
      return this.language;
    }
  }
}
</script>
