<template>
  <div class="k-block-form">
    <k-fieldset ref="fields"
      :autofocus="true"
      :fields="fields"
      :value="value"
      class="k-block-fields"
      @input="$emit('input', $event)"
    />
  </div>
</template>

<script>
export default {
  props: {
    fields: Object,
    value: Object,
  },
  methods: {
    focus() {
      this.$refs.fields.focus();
    }
  }
};
</script>

<style lang="scss">
.k-block-form {
  padding: 1.5rem;
  background: $color-background;
}

</style>
