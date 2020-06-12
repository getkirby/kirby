<template>
  <k-installation-view
    v-if="isOk"
    :installing="installing"
    :loading="loading"
    :translation="translation"
    :translations="translations"
    @translate="onTranslate"
    @install="onInstall"
  />
  <k-installation-issues-view
    v-else
    :disabled="isInstallable === false"
    v-bind="requirements"
    @retry="onRetry"
  />
</template>

<script>
export default {
  data() {
    return {
      installing: false,
      isInstallable: true,
      isOk: true,
      loading: true,
      requirements: {},
      translation: null,
      translations: [],
    };
  },
  async created() {
    this.load();
  },
  methods: {
    async load() {
      this.loading = true;

      const system = await this.$model.system.load();

      if (system.status.isInstalled === true && system.status.isReady) {
        this.$router.push("/login");
        return;
      }

      this.isInstallable = system.status.isInstallable;
      this.isOk = system.status.isOk;
      this.requirements = system.requirements;
      this.translation = this.$store.state.translation.current;
      this.translations = await this.$model.translations.options();

      this.$model.system.title(this.$t("installation"));

      this.loading = false;
    },
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
    onRetry() {
      this.load();
    },
    onTranslate(translation) {
      this.translation = translation;
      this.$store.dispatch("translation/activate", translation);
    }
  }
};
</script>
