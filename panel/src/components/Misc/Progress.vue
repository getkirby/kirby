<template>
  <progress :value="state" max="100" class="k-progress">{{ state }}%</progress>
</template>

<script>
/**
 * Validates the value to be between 0 and 100
 * @param {Number} value
 * @param {Boolean} throws whether to throw an error when validation fails
 * @returns {Boolean}
 */
const validator = (value, throws = false) => {
  if (value >= 0 && value <= 100) {
    return true;
  }

  if (throws) {
    throw new Error("value has to be between 0 and 100");
  }

  return false;
};

/**
 * A simple progress bar that we
 * mostly use it in the upload dialog.
 * @public
 *
 * @example <k-progress :value="10" />
 */
export default {
  props: {
    /**
     * Current value of the the progress bar
     * @values 0-100
     */
    value: {
      type: Number,
      default: 0,
      validator: validator
    }
  },
  data() {
    return {
      state: this.value
    };
  },
  watch: {
    value(value) {
      this.state = value;
    }
  },
  methods: {
    /**
     * Update the value
     * @param {Number} value new value of the progress bar (0-100)
     * @public
     */
    set(value) {
      validator(value, true);
      this.state = value;
    }
  }
};
</script>

<style>
.k-progress {
  -webkit-appearance: none;
  width: 100%;
  height: 0.5rem;
  border-radius: 5rem;
  background: var(--color-border);
  overflow: hidden;
  border: none;
}

.k-progress::-webkit-progress-bar {
  border: none;
  background: var(--color-border);
  height: 0.5rem;
  border-radius: 20px;
}

.k-progress::-webkit-progress-value {
  border-radius: inherit;
  background: var(--color-focus);
  transition: width 0.3s;
}

.k-progress::-moz-progress-bar {
  border-radius: inherit;
  background: var(--color-focus);
  transition: width 0.3s;
}
</style>
