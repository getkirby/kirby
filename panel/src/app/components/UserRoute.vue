<template>
  <k-user-view
    v-if="model.id"
    v-bind="view"
    @changeAvatar="onChangeAvatar"
    @changeEmail="onChangeEmail"
    @changeName="onChangeName"
    @changeLanguage="onChangeLanguage"
    @changeRole="onChangeRole"
    @input="onInput"
    @language="onLanguage"
    @remove="onRemove"
    @revert="onRevert"
    @save="onSave"
  />
</template>

<script>
import ModelRoute from "./ModelRoute.vue";
import Vue from "vue";

const load = async (id) => {
  return await Vue.$api.users.get(id, { view: "panel" });
};

export default {
  extends: ModelRoute,
  async beforeRouteEnter(to, from, next) {
    const model = await load(to.params.id);
    next(vm => {
      return vm.load(model);
    });
  },
  async beforeRouteUpdate(to, from, next) {
    const model = await load(to.params.id);
    this.load(user);
    next();
  },
  computed: {
    storeId() {
      return this.$model.users.storeId(this.model.id);
    },
    profile() {
      return {
        avatar:   this.model.avatar ? this.model.avatar.url : null,
        email:    this.model.email,
        language: this.model.language,
        role:     this.model.role.title,

        canChangeAvatar: this.lock === false,
        canChangeEmail: this.$permissions.changeEmail && this.lock === false,
        canChangeLanguage: this.$permissions.changeRole && this.lock === false,
        canChaneRole: this.$permissions.changeLanguage && this.lock === false
      };
    },
    view() {

      if (!this.model) {
        return {};
      }

      return {
        ...this.viewDefaults,
        breadcrumb: this.$model.users.breadcrumb(this.model),
        id:         this.model.id,
        options:    this.$model.users.dropdown(this.model.options),
        profile:    this.profile,
        title:      this.model.name || this.model.email,
        url:        this.model.url
      };
    }
  },
  methods: {
    onRemove() {
      this.$router.push("/users");
    },
    async onChangeAvatar() {
      await this.reload();
      this.$store.dispatch("notification/success");
    },
    onChangeEmail(user) {
      this.model.email = user.email;
    },
    onChangeLanguage(user) {
      this.model.language = user.language;
    },
    onChangeName(user) {
      this.model.name = user.name;
    },
    onChangeRole(user) {
      this.model.role = user.role;
    },
    async saveModel() {
      return await this.$model.users.update(this.model.id);
    },
    async reload() {
      const model = await load(this.mode.id);
      this.load(model);
    }
  }
}
</script>
