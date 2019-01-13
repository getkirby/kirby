<template>
  <nav v-if="hasChanges" class="k-form-buttons">
    <k-view>
      <k-button
        icon="undo"
        class="k-form-button"
        @click="reset"
      >
        {{ $t("revert") }}
      </k-button>
      <k-button
        icon="check"
        class="k-form-button"
        @click="save"
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
    id() {
      return this.$store.state.form.current;
    }
  },
  created() {
    this.$events.$on("keydown.cmd.s", this.save);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.save);
  },
  methods: {
    reset() {
      this.$store.dispatch("form/revert", this.id);
    },
    save(e) {
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
  }
};
</script>

<style lang="scss">
.k-form-buttons {
  background: $color-notice-on-dark;
}
.k-form-buttons .k-view {
  display: flex;
  justify-content: space-between;
  align-items: center;
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
</style>
