<template>
  <div>
    <p class="k-date-field-preview">
      {{ text }}
    </p>
  </div>
</template>

<script>
import DateInput from "../Input/DateInput.vue";

export default {
  props: {
    field: Object,
    value: String
  },
  computed: {
    text() {
      const dt = DateInput.methods.toDatetime.call(this, this.value);
      return dt.format(this.field.display);
    }
  }
}
</script>

<style>
.k-date-field-preview {
  padding: 0 .75rem;
}
</style>
