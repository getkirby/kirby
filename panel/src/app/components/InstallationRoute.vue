<template>
  <k-installation-view
    :installing="installing"
    :loading="loading"
    :translation="translation"
    :translations="translations"
    @translate="onTranslate"
    @install="onInstall"
  />
</template>

<script>
export default {
  data() {
    return {
      installing: false,
      translation: "en",
      translations: [],
      loading: true,
    };
  },
  async created() {
    const system = await this.$model.system.load();

    if (system.isInstalled === true && system.isReady) {
      this.$router.push("/login");
      return;
    }

    this.translation  = system.translation;
    this.translations = await this.$model.translations.options();
    this.loading = false;
  },
  methods: {
    async onInstall(values) {
      this.installing = true;
      try {
        await this.$model.system.install(values);
        this.$store.dispatch("notification/info", this.$t("welcome") + "!");
        this.$router.push("/");
      } catch (error) {
        this.$store.dispatch("notification/error", error);
      } finally {
        this.installing = false;
      }
    },
    onTranslate(translation) {
      this.translation = translation;
      this.$store.dispatch("translation/activate", translation);
    }
  }
};
</script>
