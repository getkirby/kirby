<template>
  <portal>
    <transition-group
      name="k-alerts"
      tag="ul"
      class="k-alerts"
    >
      <li
        v-for="alert in alerts"
        :key="alert.id"
      >
        <k-notification
          v-bind="alert"
          :class="getClasses(alert)"
          @click="onClick(alert)"
          @close="$emit('close', alert.id)"
        />
      </li>
    </transition-group>
  </portal>
</template>

<script>
export default {
  props: {
    alerts: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  methods: {
    getClasses(alert) {
      let classes = [
        "inline-flex",
        "rounded-sm",
        "shadow-lg",
        "text-left"
      ];

      if (alert.click) {
        classes.push("cursor-pointer");
      }

      return classes.join(" ");
    },
    onClick(alert) {
      if (alert.click) {
        alert.click();
        this.$emit('close', alert.id)
      }
    }
  }
}
</script>

<style lang="scss">
.k-alerts {
  position: fixed;
  top: .75rem;
  right: .75rem;
  text-align: right;

  li + li {
    margin-top: .5rem;
  }
  z-index: z-index(toolbar);
}
.k-alerts .k-notification {
  max-width: 20rem;
}

.k-alerts-enter-active,
.k-alerts-leave-active {
  transition: opacity .25s;
}
.k-alerts-enter,
.k-alerts-leave-to {
  opacity: 0;
}
</style>
