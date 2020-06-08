<template>
  <k-login-view
    :authenticating="authenticating"
    :loading="loading"
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
      authenticating: false,
      loading: true
    };
  },
  methods: {
    async onLogin(values) {
      this.authenticating = true;

      try {
        await this.$model.users.login(values);
        await this.$model.system.load(true);
        this.$store.dispatch("notification/info", this.$t("welcome"));

      } catch (error) {
        this.$store.dispatch("notification/error", error);

      } finally {
        this.authenticating = false;
      }
    }
  }
};
</script>
