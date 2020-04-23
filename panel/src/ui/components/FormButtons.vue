<template>
  <nav
    :data-theme="mode"
    class="k-form-buttons"
  >
    <!-- regular -->
    <template v-if="mode === 'changes'">
      <k-view>
        <k-button
          :disabled="isDisabled"
          icon="undo"
          class="k-form-button"
          @click="$refs.revert.open()"
        >
          {{ $t("revert") }}
        </k-button>
        <k-button
          :disabled="isDisabled"
          icon="check"
          class="k-form-button"
          @click="onSave"
        >
          {{ $t("save") }}
        </k-button>
      </k-view>

      <k-dialog
        ref="revert"
        :submit-button="$t('revert')"
        :text="$t('revert.confirm')"
        icon="undo"
        theme="negative"
        @submit="onRevert"
      />
    </template>

    <!-- locked -->
    <template v-if="mode === 'lock'">
      <k-view>
        <p class="k-form-lock-info">
          <k-icon type="lock" />
          <span v-html="$t('lock.isLocked', { email: lock.email })" />
        </p>

        <k-icon
          v-if="!lock.unlockable"
          type="loader"
          class="k-form-lock-loader"
        />
        <k-button
          v-else
          icon="unlock"
          class="k-form-button"
          @click="onUnlock"
        >
          {{ $t('lock.unlock') }}
        </k-button>
      </k-view>
    </template>

    <!-- unlocked -->
    <template v-if="mode === 'unlock'">
      <k-view>
        <p class="k-form-lock-info">
          {{ $t("lock.isUnlocked") }}
        </p>
        <span class="k-form-lock-buttons">
          <k-button
            icon="download"
            class="k-form-button"
            @click="onDownload"
          >
            {{ $t("download") }}
          </k-button>
          <k-button
            icon="check"
            class="k-form-button"
            @click="onResolve"
          >
            {{ $t("confirm") }}
          </k-button>
        </span>
      </k-view>
    </template>
  </nav>
</template>

<script>
export default {
  props: {
    lock: {
      type: Object,
      default() {
        return {
          email: "",
          unlockable: true
        }
      }
    },
    mode: {
      type: String,
      default: "changes"
    },
  },
  methods: {
    onDownload() {
      this.$emit("download");
    },
    onResolve() {
      this.$emit("resolve");
    },
    onRevert() {
      this.$emit("revert");
      this.$refs.revert.close();
    },
    onSave() {
      this.$emit("save");
    },
    onUnlock() {
      this.$emit("unlock");
    }
  }
};
</script>

<style lang="scss">
.k-form-buttons {
  &[data-theme="changes"] {
      background: $color-notice-on-dark;
  }

  &[data-theme="lock"] {
      background: $color-negative-on-dark;
  }

  &[data-theme="unlock"] {
      background: $color-focus-on-dark;
  }
}

.k-form-buttons .k-view {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.k-form-button.k-button {
  font-weight: 500;
  white-space: nowrap;
  line-height: 1;
  height: 2.5rem;
  display: flex;
  padding: 0 1rem;
  align-items: center;
}
.k-form-button:first-child {
  margin-left: -1rem;
}
.k-form-button:last-child {
  margin-right: -1rem;
}

.k-form-lock-info {
  display: flex;
  font-size: $text-sm;
  align-items: center;
  line-height: 1.5em;
  padding: .625rem 0;
  margin-right: 3rem;

  > .k-icon {
    margin-right: .5rem;
  }
}
.k-form-lock-buttons {
  display: flex;
  flex-shrink: 0;
}
.k-form-lock-loader {
  animation: Spin 4s linear infinite;
}
.k-form-lock-loader .k-icon-loader {
  display: flex;
}
</style>
