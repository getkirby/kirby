<template>
  <nav v-if="hasChanges" class="kirby-form-buttons">
    <kirby-button
      icon="undo"
      theme="negative"
      class="kirby-form-button"
      @click="reset"
    >
      {{ $t("revert") }}
    </kirby-button>
    <kirby-button
      icon="check"
      theme="positive"
      class="kirby-form-button"
      @click="save"
    >
      {{ $t("save") }}
    </kirby-button>
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

      if (this.$route.name === "Account") {
        return '/users/' + this.$store.state.user.current.id;
      }

      return this.$route.path;
    }
  },
  watch: {
    $route() {
      this.hasChanges = false;
    }
  },
  created() {
    this.refresh();
    this.$events.$on("key.save", this.save);
    this.$events.$on("form.changed", this.refresh);
  },
  destroyed() {
    this.$events.$off("key.save", this.save);
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
    save() {
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
  display: flex;
  align-items: center;
}
.kirby-form-button {
  font-weight: 500;
  white-space: nowrap;
  line-height: 1;
  display: flex;
  align-items: center;
}
</style>
