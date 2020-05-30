<template>
  <k-user-view
    v-if="model.id"
    v-bind="view"
    @remove="onRemoved"
  />
</template>

<script>
import ModelRoute from "./ModelRoute.vue";

export default {
  extends: ModelRoute,
  props: {
    id: {
      type: String
    }
  },
  computed: {
    storeId() {
      return "/users/" + this.id;
    },
    profile() {
      return {
        avatar:   this.model.avatar.url,
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
      return {
        ...this.viewDefaults,
        breadcrumb: this.$model.users.breadcrumb(this.model),
        id:         this.id,
        options:    this.$model.users.dropdown(this.model.options),
        profile:    this.profile,
        title:      this.model.name || this.model.email,
        url:        this.model.url
      };
    }
  },
  methods: {
    async loadModel() {
      return await this.$api.users.get(this.id);
    },
    onRemoved() {
      this.$router.push("/users");
    },
    async saveModel(values) {
      return await this.$model.users.update(this.id, values);
    }
  }
}
</script>
