<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    class="k-panel"
  >
    <keep-alive>
      <k-icons />
    </keep-alive>
    <header
      class="k-panel-header"
    >
      <k-search
        v-if="$store.state.search"
        v-bind="$store.state.search"
      />
    </header>
    <main class="k-panel-view">
      <router-view />
    </main>
    <k-form-buttons v-if="inside" />
    <k-error-dialog />

    <k-offline />

    <k-registration
      v-if="inside"
      ref="registration"
    />
  </div>
  <div
    v-else
    class="k-panel"
  >
    <main class="k-panel-view">
      <k-error-view>
        <p v-if="debug">
          {{ $store.state.system.info.error }}
        </p>
        <p v-else>
          The Panel cannot connect to the API
        </p>
      </k-error-view>
    </main>
  </div>
</template>

<script>
import Registration from "@/components/Dialogs/RegistrationDialog.vue";

export default {
  name: "App",
  components: {
    "k-registration": Registration,
  },
  data() {
    return {
      dragging: false,
      debug: this.$config.debug
    };
  },
  computed: {
    inside() {
      return !this.$route.meta.outside && this.$store.state.user.current
        ? true
        : false;
    },
    defaultTranslation() {
      if (!this.languages.current ) {
        return false;
      }

      return this.languages.current === this.languages.default;
    },
    languages() {
      return this.$store.state.languages;
    },
    translation() {
      if (!this.languages.current ) {
        return false;
      }

      return this.languages.current.code;
    }
  },
  created() {
    this.$events.$on("drop", this.drop);
  },
  destroyed() {
    this.$events.$off("drop", this.drop);
  },
  methods: {
    drop() {
      // remove any drop data from the store
      this.$store.dispatch("drag", null);
    },
    search(event) {
      event.preventDefault();
      this.$store.dispatch("search", true);
    }
  }
};
</script>

<style lang="scss">
  @import "@/ui/css/index.scss";
</style>
