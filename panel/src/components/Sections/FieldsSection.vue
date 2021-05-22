<template>
  <section class="k-fields-section">
    <k-form
      :fields="fields"
      :validate="true"
      :value="values"
      :disabled="$store.state.content.status.lock !== null"
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
    fields: Object
  },
  computed: {
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
