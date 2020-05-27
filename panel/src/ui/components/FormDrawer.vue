<template>
  <k-drawer
    ref="dialog"
    v-bind="{
      ...$props,
      autofocus: false
    }"
    v-on="listeners"
  >
    <template v-slot:context>
      <slot name="context" />
    </template>

    <k-form
      ref="form"
      v-model="value"
      :autofocus="autofocus"
      :fields="fields"
      :novalidate="novalidate"
      class="mb-3"
      @focus="onFocus"
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
    focus(fieldName) {
      this.$refs.form.focus(fieldName);
    },
    onFocus(event, field, fieldName) {
      this.$emit("focus", event, field, fieldName);
    },
    onInput() {
      this.$emit("input", this.value);
    },
    onSubmit() {
      this.$emit("submit", this.value);
    }
  }
}
</script>
