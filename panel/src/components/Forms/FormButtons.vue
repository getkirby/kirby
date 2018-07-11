<template>
  <nav v-if="hasChanges" class="kirby-form-buttons">
    <kirby-view>
      <kirby-button
        icon="undo"
        class="kirby-form-button"
        @click="reset"
      >
        {{ $t("revert") }}
      </kirby-button>
      <kirby-button
        icon="check"
        class="kirby-form-button"
        @click="save"
      >
        {{ $t("save") }}
      </kirby-button>
    </kirby-view>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      hasChanges: false
    };
  },
  computed: {
    id() {
      return this.$cache.id(this.$route, this.$store);
    }
  },
  watch: {
    $route() {
      this.hasChanges = false;
    }
  },
  created() {
    this.refresh();
    this.$events.$on("keydown.cmd.s", this.save);
    this.$events.$on("form.changed", this.refresh);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.save);
    this.$events.$off("form.changed", this.refresh);
  },
  methods: {
    refresh() {
      this.hasChanges = this.$cache.exists(this.id);
    },
    reset() {
      this.$cache.remove(this.id);
      this.$events.$emit("form.reset");
      this.refresh();
    },
    save(e) {

      if (!e) {
        return false;
      }

      if (e.preventDefault) {
        e.preventDefault();
      }

      this.$api
        .patch(this.id.substr(1), this.$cache.get(this.id))
        .then(() => {
          this.$cache.remove(this.id);
          this.$events.$emit("form.saved");
          this.$store.dispatch("notification/success", this.$t("saved"));
          this.refresh();
        })
        .catch(response => {

          if (response.details) {
            this.$store.dispatch("notification/error", {
              message: this.$t("error.form.incomplete"),
              details: response.details
            });
          } else {
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
.kirby-form-buttons {
  background: $color-focus-on-dark;
}
.kirby-form-buttons .kirby-view {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.kirby-form-button {
  font-weight: 500;
  white-space: nowrap;
  line-height: 1;
  height: 2.5rem;
  display: flex;
  padding: 0 1rem;
  align-items: center;
}
.kirby-form-button:first-child {
  margin-left: -1rem;
}
.kirby-form-button:last-child {
  margin-right: -1rem;
}
</style>
