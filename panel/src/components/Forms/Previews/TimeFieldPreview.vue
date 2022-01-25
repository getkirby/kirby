<template>
  <div>
    <p class="k-time-field-preview">
      {{ text }}
    </p>
  </div>
</template>

<script>
export default {
  props: {
    field: Object,
    value: String
  },
  computed: {
    text() {
      const dt = this.$library.dayjs.iso(this.value, "time");
      return dt?.format(this.field.display) || "";
    }
  }
};
</script>

<style>
.k-time-field-preview {
  padding: 0 0.75rem;
}
</style>
