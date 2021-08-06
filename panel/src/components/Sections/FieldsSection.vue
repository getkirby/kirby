<template>
  <section class="k-fields-section">
    <k-form
      :fields="fieldset"
      :validate="true"
      :value="values"
      :disabled="lock !== false && lock.state === 'lock'"
      @input="input"
      @submit="onSubmit"
    />
  </section>
</template>

<script>
import debounce from "@/helpers/debounce.js";

export default {
  inheritAttrs: false,
  props: {
    endpoints: Object,
    fields: Object,
    lock: Object,
    name: String,
    parent: String,
    timestamp: Number
  },
  computed: {
    fieldset() {
      let fieldset = {};

      Object.keys(this.fields).forEach(name => {
        fieldset[name] = this.fields[name];
        fieldset[name].section = this.name;
        fieldset[name].endpoints = {
          field: this.parent + "/fields/" + name,
          section: this.parent + "/sections/" + this.name,
          model: this.parent
        };
      });

      return fieldset;
    },
    values() {
      return this.$store.getters["content/values"]();
    }
  },
  created() {
    this.input = debounce(this.input, 50);
  },
  methods: {
    input(values, field, fieldName) {
      this.$store.dispatch("content/update", [
        fieldName,
        values[fieldName]
      ]);
    },
    onSubmit($event) {
      this.$events.$emit("keydown.cmd.s", $event);
    }
  }
};
</script>

<style>
.k-fields-issue-headline {
  margin-bottom: .5rem;
}
.k-fields-section input[type="submit"] {
  display: none;
}

[data-locked] .k-fields-section {
  opacity: .2;
  pointer-events: none;
}
</style>
