<template>
  <span :data-invalid="!valid" class="k-counter">
    <span>{{ count }}</span>
    <span v-if="min && max" class="k-counter-rules">({{ min }}–{{ max }})</span>
    <span v-else-if="min" class="k-counter-rules">≥ {{ min }}</span>
    <span v-else-if="max" class="k-counter-rules">≤ {{ max }}</span>
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
.k-counter {
  font-size: $font-size-tiny;
  color: $color-dark;
  font-weight: $font-weight-bold;
}
.k-counter[data-invalid] {
  color: $color-negative;
}
.k-counter-rules {
  color: $color-dark-grey;
  font-weight: $font-weight-normal;

  [dir="ltr"] & {
    padding-left: .5rem;
  }

  [dir="rtl"] & {
    padding-right: .5rem;
  }

}
</style>
