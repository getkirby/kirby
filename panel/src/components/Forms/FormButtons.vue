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
      return this.$store.getters["form/id"](this.$route);
    }
  },
  created() {
    this.$store.dispatch("form/restore");
    this.$events.$on("keydown.cmd.s", this.save);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.save);
  },
  methods: {
    reset() {
      this.$store.dispatch("form/reset", this.id);
      this.$events.$emit("form.reset");
    },
    save(e) {

      if (!e) {
        return false;
      }

      if (e.preventDefault) {
        e.preventDefault();
      }

      this.$store.dispatch("form/save", this.id)
        .then(() => {
          this.$events.$emit("model.update");
          this.$store.dispatch("form/errors", [this.id, {}]);
          this.$store.dispatch("notification/success", this.$t("saved"));
        })
        .catch(response => {

          if (response.details) {
            this.$store.dispatch("form/errors", [this.id, response.details]);
            this.$store.dispatch("notification/error", {
              message: this.$t("error.form.incomplete"),
              details: response.details
            });
          } else {
            this.$store.dispatch("form/errors", [this.id, response.message]);
            this.$store.dispatch("notification/error", {
              message: "The form could not be submitted",
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
  background: $color-focus-on-dark;
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
