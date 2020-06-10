<template>
  <k-login-view
    :loading="loading"
    :processing="processing"
    @login="onLogin"
  />
</template>

<script>
export default {
  async created() {
    const system = await this.$model.system.load();

    if (system.status.isReady !== true) {
      return this.$router.push("/installation");
    }

    const user = this.$store.state.user.current;
    if (user && user.id) {
      return this.$router.push("/");
    }

    this.$model.system.title(this.$t("login"));
    this.loading = false;
  },
  data() {
    return {
      loading: true,
      processing: false
    };
  },
  methods: {
    async onLogin(user) {
      this.processing = true;
      await this.$model.users.login(user);
      this.$router.push(this.$store.state.user.path || "/");
      this.$store.dispatch("notification/info", this.$t("welcome"));
      this.processing = false;
    }
  }
};
</script>
