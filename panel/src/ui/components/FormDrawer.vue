<template>
  <k-drawer
    ref="dialog"
    v-bind="{
      ...$props,
      autofocus: false
    }"
    v-on="listeners"
  >
    <k-form
      ref="form"
      v-model="value"
      :autofocus="true"
      :fields="fields"
      :novalidate="novalidate"
      class="mb-3"
      @input="onInput"
      @submit="onSubmit"
    />
  </k-drawer>
</template>

<script>
import Drawer from "./Drawer.vue";

export default {
  extends: Drawer,
  props: {
    fields: {
      type: [Array, Object],
      default() {
        return [];
      }
    },
    novalidate: {
      type: Boolean,
      default: false
    },
    size: {
      default: "medium"
    },
    submitButton: {
      default() {
        return this.$t("save");
      }
    },
    value: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  data() {
    return {
      listeners: {
        ...this.$listeners,
        submit: () => {
          this.$refs.form.submit();
        }
      }
    }
  },
  methods: {
    onInput(input) {
      this.$emit("input", this.value);
    },
    onSubmit(event) {
      this.$emit("submit", this.value);
    }
  }
}
</script>
