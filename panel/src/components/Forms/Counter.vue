<template>
  <span :data-invalid="!valid" class="k-counter">
    <span>{{ count }}</span>
    <span v-if="min && max" class="k-counter-rules">({{ min }}–{{ max }})</span>
    <span v-else-if="min" class="k-counter-rules">≥ {{ min }}</span>
    <span v-else-if="max" class="k-counter-rules">≤ {{ max }}</span>
  </span>
</template>

<script>
/**
 * We use the counter in many fields to show the character count of an input or the accepted min/max length of items. You can use the counter yourself very easily:
 * @example <k-counter :count="text.length" :min="2" :max="10" />
<input v-model="text">
 */
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

<style>
.k-counter {
  font-size: var(--text-xs);
  color: var(--color-gray-900);
  font-weight: var(--font-bold);
}
.k-counter[data-invalid] {
  box-shadow: none;
  border: 0;
  color: var(--color-negative);
}
.k-counter-rules {
  color: var(--color-gray-600);
  font-weight: var(--font-normal);
}
[dir="ltr"] .k-counter-rules {
  padding-left: .5rem;
}
[dir="rtl"] .k-counter-rules {
  padding-right: .5rem;
}
</style>
