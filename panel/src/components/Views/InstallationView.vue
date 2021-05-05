<template>
  <k-view v-if="system" align="center" class="k-installation-view">
    <form v-if="state === 'install'" @submit.prevent="install">
      <h1 class="k-offscreen">
        {{ $t("installation") }}
      </h1>
      <k-fieldset v-model="user" :fields="fields" :novalidate="true" />
      <k-button type="submit" icon="check">
        {{ $t("install") }}
      </k-button>
    </form>
    <k-text v-else-if="state === 'completed'">
      <k-headline>{{ $t("installation.completed") }}</k-headline>
      <k-link to="/login">
        {{ $t("login") }}
      </k-link>
    </k-text>
    <div v-else>
      <k-headline v-if="!system.isInstalled">
        {{ $t("installation.issues.headline") }}
      </k-headline>

      <ul class="k-installation-issues">
        <li v-if="system.isInstallable === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.disabled')" />
        </li>

        <li v-if="requirements.php === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.php')" />
        </li>

        <li v-if="requirements.server === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.server')" />
        </li>

        <li v-if="requirements.mbstring === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.mbstring')" />
        </li>

        <li v-if="requirements.curl === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.curl')" />
        </li>

        <li v-if="requirements.accounts === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.accounts')" />
        </li>

        <li v-if="requirements.content === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.content')" />
        </li>

        <li v-if="requirements.media === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.media')" />
        </li>

        <li v-if="requirements.sessions === false">
          <k-icon type="alert" />
          <!-- eslint-disable-next-line vue/no-v-html -->
          <span v-html="$t('installation.issues.sessions')" />
        </li>
      </ul>

      <k-button icon="refresh" @click="check">
        {{ $t('retry') }}
      </k-button>
    </div>
  </k-view>
</template>

<script>
export default {
  data() {
    return {
      user: {
        name: "",
        email: "",
        language: "",
        password: "",
        role: "admin"
      },
      languages: [],
      system: null
    };
  },
  computed: {
    state() {
      if (this.system.isOk && this.system.isInstallable && !this.system.isInstalled) {
        return 'install';
      }

      if (this.system.isOk && this.system.isInstallable && this.system.isInstalled) {
        return 'completed';
      }

      return null;
    },
    translation() {
      return this.$store.state.translation.current;
    },
    requirements() {
      return this.system && this.system.requirements ? this.system.requirements : {};
    },
    fields() {
      return {
        email: {
          label: this.$t("email"),
          type: "email",
          link: false,
          required: true
        },
        password: {
          label: this.$t("password"),
          type: "password",
          placeholder: this.$t("password") + " …",
          required: true
        },
        language: {
          label: this.$t("language"),
          type: "select",
          options: this.languages,
          icon: "globe",
          empty: false,
          required: true
        }
      };
    }
  },
  watch: {
    translation: {
      handler(value) {
        this.user.language = value;
      },
      immediate: true
    },
    "user.language"(language) {
      this.$store.dispatch("translation/activate", language);
    }
  },
  created() {
    this.$store.dispatch("content/current", null);
    this.check();
  },
  methods: {
    install() {
      this.$api.system
        .install(this.user)
        .then(user => {
          this.$store.dispatch("user/current", user);
          this.$store.dispatch("notification/success", this.$t("welcome") + "!");
          this.$go("/");
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
    check() {
      this.$store.dispatch("system/load", true).then(system => {
        if (system.isInstalled === true && system.isReady) {
          this.$go("/login");
          return;
        }

        this.$api.translations.options().then(languages => {
          this.languages = languages;

          this.system = system;
          this.$store.dispatch("title", this.$t("view.installation"));
        });
      });
    }
  }
};
</script>

<style>
.k-installation-view .k-button {
  display: block;
  margin-top: 1.5rem;
}
.k-installation-view .k-headline {
  margin-bottom: .75rem;
}
.k-installation-issues {
  line-height: 1.5em;
  font-size: var(--text-sm);
}
.k-installation-issues li {
  position: relative;
  padding: 1.5rem;
  background: var(--color-white);
}
[dir="ltr"] .k-installation-issues li {
  padding-left: 3.5rem;
}

[dir="rtl"] .k-installation-issues li {
  padding-right: 3.5rem;
}

.k-installation-issues .k-icon {
  position: absolute;
  top: calc(1.5rem + 2px);
}
[dir="ltr"] .k-installation-issues .k-icon {
  left: 1.5rem;
}

[dir="rtl"] .k-installation-issues .k-icon {
  right: 1.5rem;
}

.k-installation-issues .k-icon svg * {
  fill: var(--color-negative);
}
.k-installation-issues li:not(:last-child) {
  margin-bottom: 2px;
}
.k-installation-issues li code {
  font: inherit;
  color: var(--color-negative);
}

.k-installation-view .k-button[type="submit"] {
  padding: 1rem;
}
[dir="ltr"] .k-installation-view .k-button[type="submit"]  {
  margin-left: -1rem;
}
[dir="rtl"] .k-installation-view .k-button[type="submit"]  {
  margin-right: -1rem;
}
</style>
