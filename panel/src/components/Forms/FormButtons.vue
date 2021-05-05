<template>
  <nav :data-theme="mode" class="k-form-buttons">
    <k-view v-if="mode === 'unlock'">
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

    <k-view v-else-if="mode === 'lock'">
      <p class="k-form-lock-info">
        <k-icon type="lock" />
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span v-html="$t('lock.isLocked', { email: form.lock.email })" />
      </p>

      <k-icon
        v-if="!form.lock.unlockable"
        type="loader"
        class="k-form-lock-loader"
      />
      <k-button
        v-else
        icon="unlock"
        class="k-form-button"
        @click="setUnlock"
      >
        {{ $t('lock.unlock') }}
      </k-button>
    </k-view>

    <k-view v-else-if="mode === 'changes'">
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
      icon="undo"
      theme="negative"
      @submit="onRevert"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <k-text v-html="$t('revert.confirm')" />
    </k-dialog>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      supportsLocking: true
    }
  },
  computed: {
    api() {
      return {
        lock: [this.$route.path + "/lock", null, null, true],
        unlock: [this.$route.path + "/unlock", null, null, true]
      }
    },
    hasChanges() {
      return this.$store.getters["content/hasChanges"]();
    },
    form() {
      return {
        lock: this.$store.state.content.status.lock,
        unlock: this.$store.state.content.status.unlock
      };
    },
    id() {
      return this.$store.state.content.current;
    },
    isDisabled() {
      return this.$store.state.content.status.enabled === false;
    },
    isLocked() {
      return this.form.lock !== null;
    },
    isUnlocked() {
      return this.form.unlock !== null;
    },
    mode() {
      if (this.isUnlocked === true) {
        return "unlock";
      }

      if (this.isLocked === true) {
        return "lock";
      }

      if (this.hasChanges === true) {
        return "changes";
      }

      return null;
    }
  },
  watch: {
    hasChanges(current, previous) {
      // if user started to make changes,
      // start setting lock on each heartbeat
      if (previous === false && current === true) {
        // console.log("watch: hasChanges new -> setLock:30");
        this.$store.dispatch("heartbeat/remove", this.getLock);
        this.$store.dispatch("heartbeat/add", [this.setLock, 30]);
        return;
      }

      // if user reversed changes manually,
      // remove lock and listen to lock from other users again
      if (this.id && previous === true && current === false) {
        // console.log("watch: noChanges new -> removeLock");
        this.removeLock();
        return;
      }
    },
    id() {
      // start listening for content lock, when no changes exist
      if (this.id && this.hasChanges === false) {
        // console.log("watch: id and noChanges -> getLock:30");
        this.$store.dispatch("heartbeat/add", [this.getLock, 10]);
      }
    }
  },
  created() {
    this.$events.$on("keydown.cmd.s", this.onSave);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.onSave);
  },
  methods: {
    /**
     *  Locking API
     */

    getLock() {
      return this.$api
        .get(...this.api.lock)
        .then(response => {

          // if content locking is not supported by model,
          // set flag and stop listening
          if (response.supported === false) {
            this.supportsLocking = false;
            this.$store.dispatch("heartbeat/remove", this.getLock);
            return;
          }

          // if content is locked, dispatch info to store
          if (response.locked !== false) {
            this.$store.dispatch("content/lock", response.locked);
            return;
          }

          // if content is not locked but store still holds a lock
          // from another user, that lock has been lifted and thus
          // the content needs to be reloaded to reflect changes
          if (
            this.isLocked &&
            this.form.lock.user !== this.$store.state.user.current.id
          ) {
            this.$events.$emit("model.reload");
          }

          this.$store.dispatch("content/lock", null);
        })
        .catch(() => {
          // fail silently
        });
    },

    setLock() {
      if (this.supportsLocking === true) {
        this.$api.patch(...this.api.lock).catch(error => {
          // turns out: locking is not supported
          if (error.key === "error.lock.notImplemented") {
            this.supportsLocking = false;
            this.$store.dispatch("heartbeat/remove", this.setLock);
            return false;
          }

          // If setting lock failed, a competing lock has been set between
          // API calls. In that case, discard changes, stop setting lock and
          // listen to concurrent lock
          this.$store.dispatch("content/revert", this.id);
          this.$store.dispatch("heartbeat/remove", this.setLock);
          this.$store.dispatch("heartbeat/add", [this.getLock, 10]);
        });
      }
    },

    removeLock() {
      if (this.supportsLocking === true) {
        this.$store.dispatch("heartbeat/remove", this.setLock);

        this.$api
          .delete(...this.api.lock)
          .then(() => {
            this.$store.dispatch("content/lock", null);
            this.$store.dispatch("heartbeat/add", [this.getLock, 10]);
          })
          .catch(() => {
            // fail silently
          });
      }
    },

    setUnlock() {
      if (this.supportsLocking === true) {
        this.$store.dispatch("heartbeat/remove", this.setLock);

        this.$api
          .patch(...this.api.unlock)
          .then(() => {
            this.$store.dispatch("content/lock", null);
            this.$store.dispatch("heartbeat/add", [this.getLock, 10]);
          })
          .catch(() => {
            // fail silently
          });
      }
    },

    removeUnlock() {
      if (this.supportsLocking === true) {
        this.$store.dispatch("heartbeat/remove", this.setLock);

        this.$api
          .delete(...this.api.unlock)
          .then(() => {
            this.$store.dispatch("content/unlock", null);
            this.$store.dispatch("heartbeat/add", [this.getLock, 10]);
          })
          .catch(() => {
            // fail silently
          });
      }
    },

    /**
     *  User actions
     */

    onDownload() {
      let content = "";

      Object.keys(this.form.unlock).forEach(key => {
        content += key + ": \n\n" + this.form.unlock[key];
        content += "\n\n----\n\n";
      });

      let link = document.createElement('a');
      link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
      link.setAttribute('download', this.id + ".txt");
      link.style.display = 'none';

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },

    onResolve() {
      this.$store.dispatch("content/revert");
      this.removeUnlock();
    },

    onRevert() {
      this.$store.dispatch("content/revert");
      this.$refs.revert.close();
    },

    onSave(e) {
      if (!e) {
        return false;
      }

      if (e.preventDefault) {
        e.preventDefault();
      }

      if (this.hasChanges === false) {
        return true;
      }

      this.$store
        .dispatch("content/save")
        .then(() => {
          this.$events.$emit("model.update");
          this.$store.dispatch("notification/success", ":)");
        })
        .catch(response => {
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
              details: [{
                label: "Exception: " + response.exception,
                message: response.message
              }]
            });
          }
        });
    }
  },
};
</script>

<style>
.k-form-buttons[data-theme="changes"] {
    background: var(--color-notice-light);
}
.k-form-buttons[data-theme="lock"] {
    background: var(--color-negative-light);
}
.k-form-buttons[data-theme="unlock"] {
    background: var(--color-focus-light);
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
  font-size: var(--text-sm);
  align-items: center;
  line-height: 1.5em;
  padding: .625rem 0;
  margin-right: 3rem;

}
.k-form-lock-info > .k-icon {
  margin-right: .5rem;
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
