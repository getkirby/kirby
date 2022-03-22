<template>
  <form
    ref="form"
    method="POST"
    autocomplete="off"
    class="k-form"
    novalidate
    @submit.prevent="onSubmit"
  >
    <!-- @slot Add something above the form -->
    <slot name="header" />

    <!-- @slot If you want to replace the default fieldset -->
    <slot>
      <!-- eslint-disable vue/no-mutating-props -->
      <k-fieldset
        ref="fields"
        v-model="value"
        :disabled="disabled"
        :fields="fields"
        :novalidate="novalidate"
        v-on="listeners"
      />
    </slot>

    <!-- @slot Add something below the form -->
    <slot name="footer" />

    <input ref="submitter" class="k-form-submitter" type="submit" />
  </form>
</template>

<script>
/**
 * The Form component takes a fields definition and a value/v-model to create a full featured form with grid and everything. If you "just" need the fields, go for the `<k-fieldset>` component instead.
 */
export default {
  props: {
    /**
     * Whether the form is disabled
     */
    disabled: Boolean,
    config: Object,
    fields: {
      type: [Array, Object],
      default() {
        return {};
      }
    },
    /**
     * If `true`, form fields won't show their validation status on the fly.
     */
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
    /**
     * Focus a specific field in the form or the first one if no name is given
     * @public
     * @param  {string} name field name to focus
     */
    focus(name) {
      this.$refs.fields?.focus?.(name);
    },
    onSubmit() {
      /**
       * When the form is submitted. This can be done in most inputs by hitting enter. It can also be triggered by a field component by firing a `submit` event. This will bubble up to the form and trigger a submit there as well. This is used in the textarea component for example to link the `cmd+enter` shortcut to a submit.
       * @event submit
       * @property {object} value all field values
       */
      this.$emit("submit", this.value);
    },
    /**
     * Submit the form
     * @public
     */
    submit() {
      this.$refs.submitter.click();
    }
  }
};
</script>

<style>
.k-form-submitter {
  display: none;
}
</style>
