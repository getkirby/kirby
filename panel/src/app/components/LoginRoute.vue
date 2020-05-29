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

    if (system.isReady !== true) {
      return this.$router.push("/installation");
    }

    if (system.user && system.user.id) {
      return this.$router.push("/");
    }

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
      try {
        await this.$model.users.login(values);
        await this.$model.system.load();
        this.$store.dispatch("notification/success", this.$t("welcome"));
      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
    }
  }
};
</script>
