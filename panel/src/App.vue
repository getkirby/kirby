<template>
  <div
    v-if="!$store.state.system.info.isBroken"
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-topbar="inside"
    :data-dialog="$store.state.dialog"
    :data-translation="translation"
    :data-translation-default="defaultTranslation"
    class="k-panel"
  >
    <keep-alive>
      <k-icons />
    </keep-alive>
    <header
      v-if="inside"
      class="k-panel-header"
    >
      <k-topbar @register="$refs.registration.open()" />
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
    <div
      v-if="offline"
      class="k-offline-warning"
    >
      <p>The Panel is currently offline</p>
    </div>
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
import config from "@/config/config.js";

export default {
  name: "App",
  components: {
    "k-registration": Registration,
  },
  data() {
    return {
      offline: false,
      dragging: false,
      debug: config.debug
    };
  },
  computed: {
    inside() {
      return !this.$route.meta.outside && this.$store.state.user.current
        ? true
        : false;
    },
    defaultTranslation() {
      return this.$store.state.languages.current ? this.$store.state.languages.current === this.$store.state.languages.default : false;
    },
    translation() {
      return this.$store.state.languages.current ? this.$store.state.languages.current.code : false;
    }
  },
  created() {
    this.$events.$on("offline", this.isOffline);
    this.$events.$on("online", this.isOnline);
    this.$events.$on("keydown.cmd.shift.f", this.search);
    this.$events.$on("drop", this.drop);
  },
  destroyed() {
    this.$events.$off("offline", this.isOffline);
    this.$events.$off("online", this.isOnline);
    this.$events.$off("keydown.cmd.shift.f", this.search);
    this.$events.$off("drop", this.drop);
  },
  methods: {
    drop() {
      // remove any drop data from the store
      this.$store.dispatch("drag", null);
    },
    isOnline() {
      this.offline = false;
    },
    isOffline() {
      if (this.$store.state.system.info.isLocal === false) {
        this.offline = true;
      }
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
