<template>
  <k-section
    :name="name"
    type="fields"
  >
    <k-form
      v-bind="form"
      v-on="$listeners"
    />
  </k-section>
</template>

<script>
import Section from "./Section.vue";

export default {
  extends: Section,
  inheritAttrs: false,
  props: {
    disabled: Boolean,
    fields: Object,
  },
  computed: {
    form() {
      let fields = this.fields;

      Object.keys(this.fields).forEach(name => {
        fields[name].section = this.name;
        fields[name].endpoints = {
          field: this.api + "/fields/" + name,
          section: this.api + "/sections/" + this.name,
          model: this.api
        };
      });

      return {
        fields: fields,
        value: this.value,
        disabled: this.disabled || this.lock !== false
      }
    }
  }
};
</script>
