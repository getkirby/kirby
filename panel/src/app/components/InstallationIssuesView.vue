<template>
  <k-outside class="k-installation-issues-view">
    <k-view align="center">
      <article>
        <h1 class="flex justify-center font-bold mb-3">
          {{ $t("installation.issues.headline") }}
        </h1>

        <ul class="k-installation-issues">
          <li v-if="disabled">
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

        <footer class="flex justify-center">
          <k-button class="p-3" icon="refresh" @click="retry">
            <span v-html="$t('retry')" />
          </k-button>
        </footer>
      </article>
    </k-view>
  </k-outside>
</template>

<script>
export default {
  props: {
    disabled: {
      type: Boolean,
      default: true
    },
    requirements: {
      type: Object,
      default() {
        return {
          accounts: false,
          content: false,
          curl: false,
          mbstring: false,
          media: false,
          php: false,
          server: false,
          sessions: false,
        };
      }
    }
  },
  methods: {
    retry() {
      this.$emit("retry");
    }
  }
};
</script>

<style lang="scss">
.k-installation-issues {
  line-height: 1.5em;
  font-size: $text-sm;
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
</style>
