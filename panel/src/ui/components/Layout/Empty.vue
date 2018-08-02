<template>
  <div class="k-empty" v-on="$listeners">
    <k-icon :type="icon" size="medium" />
    <p><slot /></p>
  </div>
</template>

<script>
export default {
  props: {
    text: String,
    icon: String
  }
};
</script>

<style lang="scss">
.k-empty {
  text-align: center;
  border-left: 0;
  padding: 1.5rem;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background: $color-background;
  border-radius: $border-radius;
  color: $color-dark-grey;
  border: 1px dashed $color-border;
}
.k-empty .k-icon {
  margin-bottom: 1rem;
  color: $color-light-grey;
}
.k-empty p {
  font-size: $font-size-small;
  color: $color-light-grey;
}
</style>
