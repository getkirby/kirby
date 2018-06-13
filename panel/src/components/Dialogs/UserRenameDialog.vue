<template>
  <kirby-dialog
    ref="dialog"
    :button="$t('rename')"
    size="medium"
    theme="positive"
    @submit="$refs.form.submit()"
  >
    <kirby-form
      ref="form"
      :fields="fields"
      v-model="user"
      @submit="submit"
    />
  </kirby-dialog>
</template>

<script>
import DialogMixin from "@/mixins/dialog.js";

export default {
  mixins: [DialogMixin],
  data() {
    return {
      user: {
        id: null,
        name: null
      }
    };
  },
  computed: {
    fields() {
      return {
        name: {
          label: this.$t("user.name"),
          type: "text",
          icon: "user",
          preselect: true
        }
      };
    }
  },
  methods: {
    open(id) {
      this.$api.user.get(id, { select: ["id", "name"] }).then(user => {
        this.user = user;
        this.$refs.dialog.open();
      });
    },
    submit() {
      this.$api.user
        .changeName(this.user.id, this.user.name)
        .then(() => {
          this.success({
            message: this.$t("user.name.changed"),
            event: "user.changeName"
          });
        })
        .catch(error => {
          this.$refs.dialog.error(error.message);
        });
    }
  }
};
</script>
