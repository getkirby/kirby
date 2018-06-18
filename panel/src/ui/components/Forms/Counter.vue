<template>
  <span :data-invalid="!valid" class="kirby-counter">
    <span>{{ count }}</span>
    <span v-if="min && max" class="kirby-counter-rules">({{ min }}–{{ max }})</span>
    <span v-else-if="min" class="kirby-counter-rules">&gt;= {{ min }}</span>
    <span v-else-if="max" class="kirby-counter-rules">&lt;= {{ max }}</span>
  </span>
</template>

<script>
export default {
  props: {
    count: Number,
    min: Number,
    max: Number,
    required: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    valid() {
      if (this.required === false && this.count === 0) {
        return true;
      }

      if (this.required === true && this.count === 0) {
        return false;
      }

      if (this.min && this.count < this.min) {
        return false;
      }

      if (this.max && this.count > this.max) {
        return false;
      }

      return true;
    }
  }
};
</script>

<style lang="scss">
.kirby-counter {
  font-size: $font-size-tiny;
  color: $color-dark;
  font-weight: $font-weight-bold;
}
.kirby-counter[data-invalid] {
  color: $color-negative;
}
.kirby-counter-rules {
  padding-left: .5rem;
  color: $color-dark-grey;
  font-weight: $font-weight-normal;
}
</style>
