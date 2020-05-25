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
      loading: false
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
      return {
        endpoint: this.endpoint,
        layout: this.layout,
        max: this.max,
        multiple: this.multiple,
        options: this.options,
        search: this.search,
        size: this.size,
        toggle: this. toggle
      };
    },
    type() {
      return "models";
    }
  },
  methods: {
    open(selected) {
      this.selected = this.$helper.clone(selected);
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
      this.$emit("submit", this.selected);
      this.$refs.drawer.close();
    }
  }
}
</script>
