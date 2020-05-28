<template>
  <transition-group
    name="k-notifications"
    tag="ul"
    class="k-notifications"
  >
    <li
      v-for="notification in notifications"
      :key="notification.id"
    >
      <k-notification
        v-bind="notification"
        :class="getClasses(notification)"
        @click="onClick(notification)"
        @close="$emit('close', notification.id)"
      />
    </li>
  </transition-group>
</template>

<script>
export default {
  props: {
    notifications: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  methods: {
    getClasses(notification) {
      let classes = [
        "inline-flex",
        "rounded-sm",
        "text-left"
      ];

      if (notification.click) {
        classes.push("cursor-pointer");
      }

      return classes.join(" ");
    },
    onClick(notification) {
      if (notification.click) {
        notification.click();
        this.$emit('close', notification.id)
      }
    }
  }
}
</script>

<style lang="scss">
.k-notifications {
  position: absolute;
  top: 1rem;
  right: 3rem;
  text-align: right;

  li + li {
    margin-top: .5rem;
  }
}
.k-notifications .k-notification {
  max-width: 20rem;
}

.k-notifications-enter-active {
  transition: opacity .35s;
}
.k-notifications-leave-active {
  transition: opacity 1s;
}
.k-notifications-enter,
.k-notifications-leave-to {
  opacity: 0;
}
</style>
