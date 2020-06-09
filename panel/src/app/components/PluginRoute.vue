<template>
  <k-inside
    :breadcrumb="breadcrumb"
    :view="plugin"
    class="k-plugin-view"
  >
    <k-error-boundary :key="plugin">
      <component
        :is="'k-' + plugin + '-plugin-view'"
        @breadcrumb="onBreadcrumb"
      />
      <k-error-view
        slot="error"
        slot-scope="{ error }"
      >
        {{ error.message || error }}
      </k-error-view>
    </k-error-boundary>
  </k-inside>
</template>

<script>
export default {
  beforeRouteEnter(to, from, next) {
    next(vm => {
      vm.reload();
      vm.$store.dispatch("content/current", null);
    });
  },
  beforeRouteUpdate(to, from, next) {
    this.reload();
    next();
  },
  data() {
    return {
      breadcrumb: []
    };
  },
  computed: {
    plugin() {
      return this.$route.params.id;
    },
    view() {
      return window.panel.plugins.views[this.plugin];
    }
  },
  methods: {
    onBreadcrumb(breadcrumb) {
      this.breadcrumb = breadcrumb;
    },
    reload() {
      this.breadcrumb = [];
      this.$model.system.title(this.view.text);
    }
  }
}
</script>
