<template>
  <div>
    <p class="k-date-field-preview">
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
      const dt = this.$library.dayjs(this.value);

      if (dt) {
        let format = this.field.display;

        if (this.field.time?.display) {
          format += " " + this.field.time.display;
        }

        return dt.format(format);
      }

      return "";
    }
  }
};
</script>

<style>
.k-date-field-preview {
  padding: 0 0.75rem;
}
</style>
