<template>
  <div
    :data-dragging="$store.state.drag"
    :data-loading="$store.state.isLoading"
    :data-language="language"
    :data-language-default="defaultLanguage"
    :data-role="role"
    :data-translation="$translation.code"
    :data-user="user"
    :dir="$translation.direction"
    class="k-panel"
  >
    <slot />

    <!-- Fiber dialogs -->
    <template v-if="$store.state.dialog && $store.state.dialog.props">
      <k-fiber-dialog v-bind="dialog" />
    </template>

    <!-- Fatal iframe -->
    <k-fatal v-if="$store.state.fatal !== false" :html="$store.state.fatal" />

    <!-- Offline warning -->
    <k-offline-warning v-if="$system.isLocal === false" />

    <!-- Icons -->
    <k-icons />
  </div>
</template>

<script>
export default {
  computed: {
    defaultLanguage() {
      return this.$language ? this.$language.default : false;
    },
    dialog() {
      return this.$helper.clone(this.$store.state.dialog);
    },
    language() {
      return this.$language ? this.$language.code : null;
    },
    role() {
      return this.$user ? this.$user.role : null;
    },
    user() {
      return this.$user ? this.$user.id : null;
    }
  },
  created() {
    this.$events.$on("drop", this.drop);
  },
  destroyed() {
    this.$events.$off("drop", this.drop);
  },
  methods: {
    drop() {
      // remove any drop data from the store
      this.$store.dispatch("drag", null);
    }
  }
};
</script>

<style>
.k-panel[data-loading="true"] {
  animation: LoadingCursor 0.5s;
}
.k-panel[data-loading="true"]::after,
.k-panel[data-dragging="true"] {
  user-select: none;
}
</style>
