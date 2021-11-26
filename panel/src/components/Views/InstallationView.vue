<template>
  <k-panel>
    <k-view align="center" class="k-installation-view">
      <!-- installation complete -->
      <k-text v-if="isComplete">
        <k-headline>{{ $t("installation.completed") }}</k-headline>
        <k-link to="/login">
          {{ $t("login") }}
        </k-link>
      </k-text>

      <!-- ready to be installed -->
      <form v-else-if="isReady" @submit.prevent="install">
        <h1 class="sr-only">
          {{ $t("installation") }}
        </h1>
        <k-fieldset v-model="user" :fields="fields" :novalidate="true" />
        <k-button :text="$t('install')" type="submit" icon="check" />
      </form>

      <!-- not meeting requirements -->
      <div v-else>
        <k-headline>
          {{ $t("installation.issues.headline") }}
        </k-headline>

        <ul class="k-installation-issues">
          <li v-if="isInstallable === false">
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

        <k-button :text="$t('retry')" icon="refresh" @click="$reload" />
      </div>
    </k-view>
  </k-panel>
</template>

<script>
export default {
  props: {
    isInstallable: Boolean,
    isInstalled: Boolean,
    isOk: Boolean,
    requirements: Object,
    translations: Array
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
          label: this.$t("email"),
          type: "email",
          link: false,
          autofocus: true,
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
    isReady() {
      return this.isOk && this.isInstallable;
    },
    isComplete() {
      return this.isOk && this.isInstalled;
    }
  },
  methods: {
    async install() {
      try {
        await this.$api.system.install(this.user);
        await this.$reload({
          globals: ["$system", "$translation"]
        });

        this.$store.dispatch("notification/success", this.$t("welcome") + "!");
      } catch (error) {
        this.$store.dispatch("notification/error", error);
      }
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
  margin-bottom: 0.75rem;
}
.k-installation-issues {
  line-height: 1.5em;
  font-size: var(--text-sm);
}
.k-installation-issues li {
  position: relative;
  padding: 1.5rem;
  background: var(--color-white);
  padding-inline-start: 3.5rem;
}

.k-installation-issues .k-icon {
  position: absolute;
  top: calc(1.5rem + 2px);
  inset-inline-start: 1.5rem;
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
  margin-inline-start: -1rem;
}
</style>
