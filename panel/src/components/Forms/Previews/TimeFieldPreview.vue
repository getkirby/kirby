<template>
  <div>
    <p class="k-time-field-preview">
      {{ text }}
    </p>
  </div>
</template>

<script>
import TimeInput from "../Input/TimeInput.vue";

export default {
  props: {
    field: Object,
    value: String
  },
  computed: {
    text() {
      const dt = TimeInput.methods.toDatetime.call(this, this.value);
      return dt.format(this.field.display);
    }
  }
}
</script>

<style>
.k-time-field-preview {
  padding: 0 .75rem;
}
</style>
