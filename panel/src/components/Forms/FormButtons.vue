<template>
  <nav class="k-form-buttons">
    <k-view v-if="hasLock === true" theme="lock">
      <k-text class="k-form-lock-info">
        <k-icon type="lock" />
        <span v-html="$t('lock.isLocked', { email: form.lock.email })" />
      </k-text>
      <k-button
        :disabled="!form.lock.canUnlock"
        icon="unlock"
        class="k-form-button"
        @click="setUnlock"
      >
        {{ $t('lock.unlock') }}
      </k-button>
    </k-view>

    <k-view v-else-if="hasUnlock === true" theme="unlock">
      <k-text>
        {{ $t("lock.isUnlocked") }}
      </k-text>
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
    </k-view>

    <k-view v-else-if="hasChanges === true" theme="changes">
      <k-button
        :disabled="isDisabled"
        icon="undo"
        class="k-form-button"
        @click="onRevert"
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
  </nav>
</template>

<script>
export default {
  computed: {
    hasChanges() {
      return this.$store.getters["form/hasChanges"](this.id);
    },
    hasLock() {
      return this.form.lock !== null;
    },
    hasUnlock() {
      return this.form.unlock !== null;
    },
    form() {
      return {
        lock: this.$store.getters["form/lock"],
        unlock: this.$store.getters["form/unlock"]
      };
    },
    id() {
      return this.$store.getters["form/current"];
    },
    isDisabled() {
      return this.$store.getters["form/isDisabled"];
    }
  },
  watch: {
    hasChanges(current, previous) {
      // if user started to make changes,
      // start setting lock on each heartbeat
      if (current === true && previous == false) {
        this.$store.dispatch("heartbeat/remove", this.listen);
        this.$store.dispatch("heartbeat/add", [this.setLock, 30]);

      // if user reversed changes manually,
      // remove lock and listen to lock from other users again
      } else if (this.id && current === false && previous == true) {
        this.removeLock();
      }
    },
    id() {
      // whenever the model id changes,
      // make sure to remove heartbeats
      if (!this.id) {
        this.$store.dispatch("heartbeat/remove", this.listen);
        this.$store.dispatch("heartbeat/remove", this.setLock);
        return;
      }

      if (this.hasChanges === false) {
        this.$store.dispatch("heartbeat/add", this.listen);
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
    listen() {
      return this.$api.get(this.$route.path + "/lock", null, null, true).then(response => {

        // if content is locked, dispatch info to store
        if (response.locked === true) {
          this.$store.dispatch("form/lock", {
            user: response.user,
            email: response.email,
            time: parseInt(response.time, 10),
            canUnlock: response.canUnlock
          });
          return;
        }

        // if content is not locked but store still holds a lock
        // from a another user, that lock has been lifted and thus
        // the content needs to be reloaded to reflect any changes
        if (
          this.hasLock &&
          this.form.lock.user !== this.$store.state.user.current.id
        ) {
          this.$events.$emit("model.reload");
        }

        this.$store.dispatch("form/lock", null);
      });
    },
    setLock() {
      this.$api.patch(this.$route.path + "/lock", null, null, true).catch(() => {
        this.$store.dispatch("form/revert", this.id);
        this.$store.dispatch("heartbeat/remove", this.setLock);
        this.$store.dispatch("heartbeat/add", this.listen);
      });
    },
    removeLock() {
      this.$store.dispatch("heartbeat/remove", this.setLock);
      this.$api.delete(this.$route.path + "/lock", null, null, true).then(() => {
        this.$store.dispatch("form/lock", null);
        this.$store.dispatch("heartbeat/add", this.listen);
      });
    },
    setUnlock(user) {
      this.$store.dispatch("heartbeat/remove", this.setLock);
      this.$api.patch(this.$route.path + "/unlock", null, null, true).then(() => {
        this.$store.dispatch("form/lock", null);
        this.$store.dispatch("heartbeat/add", this.listen);
      });
    },
    removeUnlock() {
      this.$store.dispatch("heartbeat/remove", this.setLock);
      this.$api.delete(this.$route.path + "/unlock", null, null, true).then(() => {
        this.$store.dispatch("form/unlock", null);
        this.$store.dispatch("heartbeat/add", this.listen);
      });
    },
    onDownload() {
      let content = "";

      Object.keys(this.form.unlock).forEach(key => {
        content += key + ": \n" + this.form.unlock[key];
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
      this.$store.dispatch("form/revert", this.id);
      this.removeUnlock();
    },
    onRevert() {
      this.$store.dispatch("form/revert", this.id);
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
        .dispatch("form/save", this.id)
        .then(() => {
          this.$events.$emit("model.update");
          this.$store.dispatch("notification/success", ":)");
        })
        .catch(response => {
          if (response.code === 403) {
            return;
          }

          if (response.details) {
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
        });
    }
  },
};
</script>

<style lang="scss">
.k-form-buttons .k-view {
  display: flex;
  justify-content: space-between;
  align-items: center;

  &[theme="changes"] {
      background: $color-notice-on-dark;
  }

  &[theme="lock"] {
      background: $color-negative-on-dark;
  }

  &[theme="unlock"] {
      background: $color-focus-on-dark;
  }
}
.k-form-button {
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

  > .k-icon {
    margin-right: .5rem;
  }
}
</style>
