<template>
  <k-login-view />
</template>

<script>
export default {
  async beforeRouteEnter(to, from, next) {
    const system = await this.$model.system.load();

    if (system.isReady !== true) {
      next("/installation");
    }

    if (system.user && system.user.id) {
      next("/");
    }

    this.$store.dispatch("title", this.$t("login"));
    next();
  }
};
</script>
