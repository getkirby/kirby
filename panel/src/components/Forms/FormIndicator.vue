<template>
  <k-dropdown v-if="hasChanges" class="k-form-indicator">
    <k-button
      class="k-form-indicator-toggle k-topbar-button"
      icon="edit"
      @click="toggle"
    />
    <k-dropdown-content ref="list" align="right" theme="light">
      <p class="k-form-indicator-info">{{ $t("lock.unsaved") }}:</p>
      <hr />
      <k-dropdown-item
        v-for="option in options"
        :key="option.id"
        v-bind="option"
      >
        {{ option.text }}
      </k-dropdown-item>
    </k-dropdown-content>
  </k-dropdown>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
      options: []
    };
  },
  computed: {
    hasChanges() {
      return this.ids.length > 0;
    },
    ids() {
      return Object.keys(this.store).filter((id) => {
        return Object.keys(this.store[id]?.changes || {}).length > 0;
      });
    },
    store() {
      return this.$store.state.content.models;
    }
  },
  methods: {
    async toggle() {
      if (this.$refs.list.isOpen === false) {
        try {
          await this.$dropdown("changes", {
            method: "POST",
            body: {
              ids: this.ids
            }
          })((options) => {
            this.options = options;
          });
        } catch (e) {
          this.$store.dispatch(
            "notification/success",
            this.$t("lock.unsaved.empty")
          );
          this.$store.dispatch("content/clear");
          return false;
        }
      }

      if (this.$refs.list) {
        this.$refs.list.toggle();
      }
    }
  }
};
</script>

<style>
.k-form-indicator-toggle {
  color: var(--color-notice-light);
}
.k-form-indicator-info {
  font-size: var(--text-sm);
  font-weight: var(--font-bold);
  padding: 0.75rem 1rem 0.25rem;
  line-height: 1.25em;
  width: 15rem;
}
</style>
