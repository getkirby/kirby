<template>
  <k-outside>
    <k-view align="center" class="k-installation-view">
      <form v-if="state === 'install'" @submit.prevent="install">
        <h1 class="k-offscreen">{{ $t("installation") }}</h1>
        <k-fieldset :fields="fields" :novalidate="true" v-model="user" />
        <k-button type="submit" icon="check">{{ $t("install") }}</k-button>
      </form>
      <div v-else>
        <k-headline v-if="!isInstalled">{{ $t("installation.issues.headline") }}</k-headline>

        <ul class="k-installation-issues">
          <li v-if="isInstallable === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.disabled')" />
          </li>

          <li v-if="requirements.php === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.php')" />
          </li>

          <li v-if="requirements.server === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.server')" />
          </li>

          <li v-if="requirements.mbstring === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.mbstring')" />
          </li>

          <li v-if="requirements.curl === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.curl')" />
          </li>

          <li v-if="requirements.accounts === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.accounts')" />
          </li>

          <li v-if="requirements.content === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.content')" />
          </li>

          <li v-if="requirements.media === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.media')" />
          </li>

          <li v-if="requirements.sessions === false">
            <k-icon type="alert" />
            <span v-html="$t('installation.issues.sessions')" />
          </li>

        </ul>

        <k-button icon="refresh" @click="$reload"><span v-html="$t('retry')" /></k-button>

      </div>
    </k-view>
  </k-outside>
</template>

<script>

export default {
  props: {
    isInstallable: Boolean,
    isInstalled: Boolean,
    isOk: Boolean,
    requirements: Object,
    translations: Array,
  },
  data() {
    return {
      user: {
        name: "",
        email: "",
        language: this.$translation.code,
        password: "",
        role: "admin"
      }
    };
  },
  computed: {
    fields() {
      return {
        email: {
          autofocus: true,
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
          options: this.translations,
          icon: "globe",
          empty: false,
          required: true
        }
      };
    },
    state() {
      if (this.isOk && this.isInstallable && !this.isInstalled) {
        return "install";
      }

      return "requirements";
    }
  },
  methods: {
    install() {
      this.$api.system
        .install(this.user)
        .then(user => {
          this.$store.dispatch("notification/success", this.$t("welcome") + "!");
          this.$go("/");
        })
        .catch(error => {
          this.$store.dispatch("notification/error", error);
        });
    },
  }
};
</script>

<style lang="scss">
.k-installation-view .k-button {
  display: block;
  margin-top: 1.5rem;
}
.k-installation-view .k-headline {
  margin-bottom: .75rem;
}
.k-installation-issues {
  line-height: 1.5em;
  font-size: $font-size-small;
}
.k-installation-issues li {
  position: relative;
  padding: 1.5rem;
  background: $color-white;

  [dir="ltr"] & {
    padding-left: 3.5rem;
  }

  [dir="rtl"] & {
    padding-right: 3.5rem;
  }

}
.k-installation-issues .k-icon {
  position: absolute;
  top: calc(1.5rem + 2px);

  [dir="ltr"] & {
    left: 1.5rem;
  }

  [dir="rtl"] & {
    right: 1.5rem;
  }
}

.k-installation-issues .k-icon svg * {
  fill: $color-negative;
}
.k-installation-issues li:not(:last-child) {
  margin-bottom: 2px;
}
.k-installation-issues li code {
  font: inherit;
  color: $color-negative;
}

.k-installation-view .k-button[type="submit"] {
  padding: 1rem;

  [dir="ltr"] & {
    margin-left: -1rem;
  }

  [dir="rtl"] & {
    margin-right: -1rem;
  }
}
</style>
