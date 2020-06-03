<script>
import AsyncRemoveDialog from "@/ui/components/AsyncRemoveDialog.vue";

export default {
  extends: AsyncRemoveDialog,
  methods: {
    async load(id) {
      this.id   = id;
      this.user = await this.$api.users.get(id);
      this.text = this.$t('user.delete.confirm', {
        email: this.user.email
      });
    },
    async submit() {
      await this.$model.users.delete(this.id);
      return this.user;
    }
  }
}
</script>
