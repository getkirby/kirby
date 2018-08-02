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
    this.$events.$on("form.change", this.refresh);
  },
  destroyed() {
    this.$events.$off("keydown.cmd.s", this.save);
    this.$events.$off("form.change", this.refresh);
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
          this.$events.$emit("form.save");
          this.$events.$emit("model.update");
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
