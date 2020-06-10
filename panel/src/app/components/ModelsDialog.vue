<template>
  <k-drawer
    ref="drawer"
    :loading="loading"
    :title="label"
    :size="width"
    @submit="onSubmit"
    @close="$refs.picker.reset()"
  >
    <component
      :is="'k-' + type + '-picker'"
      ref="picker"
      v-model="selected"
      v-bind="picker"
      v-on="$listeners"
      @startLoading="onLoading"
      @stopLoading="onLoaded"
    />
  </k-drawer>
</template>

<script>
import ModelsPicker from "./ModelsPicker.vue";

export default {
  extends: ModelsPicker,
  props: {
    size: {
      type: String,
    },
    title: {
      type: String
    },
    width: {
      type: String,
      default: "small"
    }
  },
  data() {
    return {
      selected: [],
      loading: false,
      legacy: {}
    }
  },
  computed: {
    label() {
      if (this.title) {
        return this.title;
      }

      if (["files", "pages", "users"].includes(this.type)) {
        return this.$t(this.type) + " / " + this.$t("select");
      }

      return this.$t("select");
    },
    picker() {
      // <3.4 legacy support for options that were not passed as
      // props but instead through the open(options) method
      return {
        endpoint: this.legacy.endpoint || this.endpoint,
        layout:   this.legacy.layout   || this.layout,
        max:      this.legacy.max      || this.max,
        multiple: this.legacy.multiple || this.multiple,
        options:  this.legacy.options  || this.options,
        search:   this.legacy.search   || this.search,
        size:     this.legacy.size     || this.size,
        toggle:   this.legacy.toggle   || this.toggle
      };
    },
    type() {
      return "models";
    }
  },
  methods: {
    open(selected = []) {
      if (Array.isArray(selected)) {
        this.selected = this.$helper.clone(selected);
      } else {
        // TODO: deprecated. Remove in Kirby 3.6
        console.warn("ModelsDialog: Passing options via the `open()` method has been deprecated and will be removed in a future release. Pass options as attributes on the component instead.")
        this.selected = this.$helper.clone(selected.selected || []);
        this.legacy   = selected;
      }

      this.$refs.drawer.open();
      setTimeout(() => {
        if (this.$refs.picker.$refs.search) {
          this.$refs.picker.$refs.search.focus();
        }
      }, 50);
    },
    onLoading() {
      this.loading = true;
    },
    onLoaded() {
      this.loading = false;
    },
    onSubmit() {
      this.$emit("submit", this.selected.map(item => ({ id: item })));
      this.$refs.drawer.close();
    }
  }
}
</script>
