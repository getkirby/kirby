<template>
  <progress :value="state" max="100" class="k-progress">
    {{ state }}%
  </progress>
</template>

<script>
export default {
  props: {
    value: {
      type: Number,
      default: 0
    }
  },
  data() {
    return {
      state: this.value
    };
  },
  methods: {
    set(value) {
      this.state = value;
    }
  }
};
</script>

<style lang="scss">
.k-progress {
  -webkit-appearance: none;
  width: 100%;
  height: 0.5rem;
  border-radius: 5rem;
}

.k-progress::-webkit-progress-bar {
  border: none;
  background: $color-border;
  height: 0.5rem;
  border-radius: 20px;
}

.k-progress::-webkit-progress-value {
  border-radius: 20px;
  background: $color-focus;
  transition: width 0.3s;
}
</style>
