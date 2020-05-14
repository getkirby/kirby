<template>
  <form
    ref="form"
    :novalidate="novalidate"
    method="POST"
    autocomplete="off"
    class="k-form"
    @submit.prevent="onSubmit"
  >
    <slot name="header" />
    <slot>
      <k-fieldset
        ref="fields"
        :value="value"
        :autofocus="autofocus"
        :disabled="disabled"
        :fields="fields"
        :novalidate="novalidate"
        v-on="listeners"
      />
    </slot>
    <slot name="footer" />
    <input
      ref="submitter"
      class="k-form-submitter hidden"
      type="submit"
    >
  </form>
</template>

<script>
export default {
  props: {
    autofocus: {
      type: Boolean,
      default: false,
    },
    disabled: Boolean,
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
      listeners: {
        ...this.$listeners,
        input: this.onInput,
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
    onInput(values) {
      this.$emit("input", values);
    },
    onSubmit(values) {
      this.$emit("submit", values);
    },
    submit() {
      this.$refs.submitter.click();
    }
  }
};
</script>
