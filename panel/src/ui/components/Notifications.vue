<template>
  <portal>
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
  </portal>
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
        "shadow-lg",
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
  position: fixed;
  top: .75rem;
  right: .75rem;
  text-align: right;

  li + li {
    margin-top: .5rem;
  }
  z-index: z-index(toolbar);
}
.k-notifications .k-notification {
  max-width: 20rem;
}

.k-notifications-enter-active {
  transition: opacity .25s;
}
.k-notifications-leave-active {
  transition: opacity .25s;
}
.k-notifications-enter,
.k-notifications-leave-to {
  opacity: 0;
}
</style>
