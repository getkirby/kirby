<template>
  <nav :data-theme="theme" class="k-form-buttons">
    <k-view v-if="mode === 'unlock'">
      <p class="k-form-lock-info">
        {{ $t("lock.isUnlocked") }}
      </p>
      <span class="k-form-lock-buttons">
        <k-button
          :text="$t('download')"
          icon="download"
          class="k-form-button"
          @click="onDownload"
        />
        <k-button
          :text="$t('confirm')"
          icon="check"
          class="k-form-button"
          @click="onResolve"
        />
      </span>
    </k-view>

    <k-view v-else-if="mode === 'lock'">
      <p class="k-form-lock-info">
        <k-icon type="lock" />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-html="$t('lock.isLocked', { email: $esc(lock.data.email) })" />
      </p>

      <k-icon
        v-if="!lock.data.unlockable"
        type="loader"
        class="k-form-lock-loader"
      />
      <k-button
        v-else
        :text="$t('lock.unlock')"
        icon="unlock"
        class="k-form-button"
        @click="onUnlock()"
      />
    </k-view>

    <k-view v-else-if="mode === 'changes'">
      <k-button
        :disabled="isDisabled"
        :text="$t('revert')"
        icon="undo"
        class="k-form-button"
        @click="onRevert"
      />
      <k-button
        :disabled="isDisabled"
        :text="$t('save')"
        icon="check"
        class="k-form-button"
        @click="onSave"
      />
    </k-view>

    <k-dialog
      ref="revert"
      :submit-button="$t('revert')"
      icon="undo"
      theme="negative"
      @submit="revert"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="$t('revert.confirm')" />
    </k-dialog>
  </nav>
</template>

<script>
export default {
  props: {
    lock: [Boolean, Object]
  },
  data() {
    return {
      isRefreshing: null,
      isLocking: null
    };
  },
  computed: {
    hasChanges() {
      return this.$store.getters["content/hasChanges"]();
    },
    isDisabled() {
      return this.$store.state.content.status.enabled === false;
    },
    isLocked() {
      return this.lockState === "lock";
    },
    isUnlocked() {
      return this.lockState === "unlock";
    },
    mode() {
      if (this.lockState !== null) {
        return this.lockState;
      }

      if (this.hasChanges === true) {
        return "changes";
      }

      return null;
    },
    lockState() {
      return this.supportsLocking && this.lock ? this.lock.state : null;
    },
    supportsLocking() {
      return this.lock !== false;
    },
    theme() {
      if (this.mode === "lock") {
        return "negative";
      }
      if (this.mode === "unlock") {
        return "info";
      }

      return "notice";
    }
  },
  watch: {
    hasChanges: {
      handler(changes, before) {
        if (this.supportsLocking === true) {
          if (this.isLocked === false && this.isUnlocked === false) {
            if (changes === true) {
              // unsaved changes, write lock every 30 seconds
              this.onLock();
              this.isLocking = setInterval(this.onLock, 30000);
            } else if (before) {
              // no more unsaved changes, stop writing lock, remove lock
              clearInterval(this.isLocking);
              this.onLock(false);
            }
          }
        }
      },
      immediate: true
    },
    isLocked(locked) {
      // model used to be locked by another user,
      // lock has been lifted, so refresh data
      if (locked === false) {
        this.$events.$emit("model.reload");
      }
    }
  },
  created() {
    // refresh lock data every 10 seconds
    if (this.supportsLocking) {
      this.isRefreshing = setInterval(this.check, 10000);
    }
    this.$events.$on("keydown.cmd.s", this.onSave);
  },
  destroyed() {
    // make sure to clear all intervals
    clearInterval(this.isRefreshing);
    clearInterval(this.isLocking);
    this.$events.$off("keydown.cmd.s", this.onSave);
  },
  methods: {
    check() {
      this.$reload({
        navigate: false,
        only: "$view.props.lock",
        silent: true
      });
    },
    async onLock(lock = true) {
      const api = [this.$view.path + "/lock", null, null, true];

      // writing lock
      if (lock === true) {
        try {
          await this.$api.patch(...api);
        } catch (error) {
          // If setting lock failed, a competing lock has been set between
          // API calls. In that case, discard changes, stop setting lock
          clearInterval(this.isLocking);
          this.$store.dispatch("content/revert");
        }
      }

      // removing lock
      else {
        clearInterval(this.isLocking);
        await this.$api.delete(...api);
      }
    },
    /**
     * Download unsaved changes after model got unlocked
     */
    onDownload() {
      let content = "";
      const changes = this.$store.getters["content/changes"]();

      Object.keys(changes).forEach((key) => {
        content += key + ": \n\n" + changes[key];
        content += "\n\n----\n\n";
      });

      let link = document.createElement("a");
      link.setAttribute(
        "href",
        "data:text/plain;charset=utf-8," + encodeURIComponent(content)
      );
      link.setAttribute("download", this.$view.path + ".txt");
      link.style.display = "none";

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    async onResolve() {
      // remove content unlock and throw away unsaved changes
      await this.onUnlock(false);
      this.$store.dispatch("content/revert");
    },
    onRevert() {
      this.$refs.revert.open();
    },
    async onSave(e) {
      if (!e) {
        return false;
      }

      if (e.preventDefault) {
        e.preventDefault();
      }

      try {
        await this.$store.dispatch("content/save");
        this.$events.$emit("model.update");
        this.$store.dispatch("notification/success", ":)");
      } catch (response) {
        if (response.code === 403) {
          return;
        }

        if (response.details && Object.keys(response.details).length > 0) {
          this.$store.dispatch("notification/error", {
            message: this.$t("error.form.incomplete"),
            details: response.details
          });
        } else {
          this.$store.dispatch("notification/error", {
            message: this.$t("error.form.notSaved"),
            details: [
              {
                label: "Exception: " + response.exception,
                message: response.message
              }
            ]
          });
        }
      }
    },
    async onUnlock(unlock = true) {
      const api = [this.$view.path + "/unlock", null, null, true];

      if (unlock === true) {
        // unlocking (writing unlock)
        await this.$api.patch(...api);
      } else {
        // resolving unlock (removing unlock)
        await this.$api.delete(...api);
      }

      this.$reload({ silent: true });
    },
    revert() {
      this.$store.dispatch("content/revert");
      this.$refs.revert.close();
    }
  }
};
</script>

<style>
.k-form-buttons[data-theme] {
  background: var(--theme-light);
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
  margin-inline-start: -1rem;
}
.k-form-button:last-child {
  margin-inline-end: -1rem;
}

.k-form-lock-info {
  display: flex;
  font-size: var(--text-sm);
  align-items: center;
  line-height: 1.5em;
  padding: 0.625rem 0;
  margin-inline-end: 3rem;
}
.k-form-lock-info > .k-icon {
  margin-inline-end: 0.5rem;
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
