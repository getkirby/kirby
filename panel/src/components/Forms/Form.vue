<template>
  <form
    ref="form"
    method="POST"
    autocomplete="off"
    class="k-form"
    novalidate
    @submit.prevent="onSubmit"
  >
    <slot name="header"/>
    <slot>
      <k-fieldset
        ref="fields"
        :disabled="disabled"
        :fields="fields"
        :novalidate="novalidate"
        v-model="value"
        v-on="listeners"
      />
    </slot>
    <slot name="footer"/>
    <input ref="submitter" class="k-form-submitter" type="submit">
  </form>
</template>

<script>
export default {
  props: {
    disabled: Boolean,
    config: Object,
    fields: {
      type: [Array, Object],
      default() {
        return {};
      }
    },
    novalidate: {
      type: Boolean,
      default: false
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
      errors: {},
      listeners: {
        ...this.$listeners,
        submit: this.onSubmit
      }
    };
  },
  methods: {
    focus(name) {
      if (this.$refs.fields && this.$refs.fields.focus) {
        this.$refs.fields.focus(name);
      }
    },
    onSubmit() {
      this.$emit("submit", this.value);
    },
    submit() {
      this.$refs.submitter.click();
    }
  }
};
</script>

<style lang="scss">
.k-form-submitter {
  display: none;
}
</style>
