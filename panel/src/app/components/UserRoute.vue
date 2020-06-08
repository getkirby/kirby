<template>
  <k-user-view
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
    next(vm => vm.load(model));
  },
  async beforeRouteUpdate(to, from, next) {
    const model = await load(to.params.id);
    this.load(model);
    next();
  },
  computed: {
    next() {
      if (!this.model.next) return false;

      return {
        link: this.$model.users.link(this.model.next.id),
        tooltip: this.model.next.username
      };
    },
    prev() {
      if (!this.model.prev) return false;

      return {
        link: this.$model.users.link(this.model.prev.id),
        tooltip: this.model.prev.username
      };
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
    storeId() {
      return this.$model.users.storeId(this.model.id);
    },
    view() {
      if (!this.model) {
        return {};
      }

      return {
        ...this.viewDefaults,
        api:        this.$model.users.url(this.model.id),
        breadcrumb: this.$model.users.breadcrumb(this.model),
        id:         this.model.id,
        next:       this.next,
        options:    this.$model.users.dropdown(this.model.options),
        prev:       this.prev,
        profile:    this.profile,
        title:      this.model.name || this.model.email,
        url:        this.model.url
      };
    }
  },
  methods: {
    onRemove() {
      const path = this.$model.users.link();
      this.$router.push(path);
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
    onTitle() {
      this.$model.system.title(this.model.name || this.model.email);
    },
    async reload() {
      const model = await load(this.model.id);
      this.load(model);
    },
    async save() {
      return await this.$model.users.update(this.model.id);
    }
  }
}
</script>
