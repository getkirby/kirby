<template>
  <kirby-view v-if="system" align="center" class="kirby-installation-view">
    <form v-if="system.isOk && !system.isInstalled" @submit.prevent="install">
      <kirby-fieldset :fields="fields" v-model="user" />
      <kirby-button type="submit" icon="check">{{ $t("install") }}</kirby-button>
    </form>
    <kirby-text v-else-if="system.isInstalled">
      <kirby-headline>The panel is already installed</kirby-headline>
      <kirby-link to="/login">Login now</kirby-link>
    </kirby-text>
    <div v-else>
      <kirby-headline>{{ $t("installation.issues.headline") }}</kirby-headline>

      <ul class="kirby-installation-issues">
        <li v-if="requirements.php === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.php')" />
        </li>

        <li v-if="requirements.server === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.server')" />
        </li>

        <li v-if="requirements.mbstring === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.mbstring')" />
        </li>

        <li v-if="requirements.curl === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.curl')" />
        </li>

        <li v-if="requirements.accounts === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.accounts')" />
        </li>

        <li v-if="requirements.content === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.content')" />
        </li>

        <li v-if="requirements.media === false">
          <kirby-icon type="alert" />
          <span v-html="$t('installation.issues.media')" />
        </li>

      </ul>

      <kirby-button icon="refresh" @click="check"><span v-html="$t('retry')" /></kirby-button>

    </div>
  </kirby-view>
</template>

<script>
export default {
  data() {
    return {
      user: {
        email: "",
        language: "en",
        password: "",
        role: "admin"
      },
      languages: [],
      system: null
    };
  },
  computed: {
    requirements() {
      return this.system ? this.system.requirements : {};
    },
    fields() {
      return {
        email: {
          label: this.$t("user.email"),
          type: "email",
          link: false,
          placeholder: this.$t("user.email.placeholder")
        },
        password: {
          label: this.$t("user.password"),
          type: "password",
          placeholder: this.$t("user.password") + " …",
          minLength: 8
        },
        language: {
          label: this.$t("user.language"),
          type: "select",
          options: this.languages,
          icon: "globe",
          empty: false
        }
      };
    }
  },
  watch: {
    "user.language"(language) {
      this.$store.dispatch("translation/activate", language);
    }
  },
  created() {
    this.check();
  },
  methods: {
    install() {
      this.$api.system
        .install(this.user)
        .then(user => {
          this.$store.dispatch("user/current", user);
          this.$store.dispatch("notification/success", "Welcome!");
          this.$router.push("/");
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    check() {
      this.$store.dispatch("system/load", true).then(system => {
        if (system.isInstalled === true) {
          this.$router.push("/login");
        }

        this.$api.translation.options().then(languages => {
          this.languages = languages;

          this.system = system;
          this.$store.dispatch("title", this.$t("view.installation"));
        });
      });
    }
  }
};
</script>

<style lang="scss">
.kirby-installation-view .kirby-button {
  display: block;
  margin-top: 1.5rem;
}

.kirby-installation-issues {
  line-height: 1.5em;
  font-size: $font-size-small;
}
.kirby-installation-issues li {
  position: relative;
  padding: 1.5rem;
  padding-left: 3.5rem;
  background: $color-white;
}
.kirby-installation-issues .kirby-icon {
  position: absolute;
  top: calc(1.5rem + 2px);
  left: 1.5rem;
}
.kirby-installation-issues .kirby-icon svg * {
  fill: $color-negative;
}
.kirby-installation-issues li:not(:last-child) {
  margin-bottom: 2px;
}
.kirby-installation-issues li code {
  font: inherit;
  color: $color-negative;
}
</style>
